<?php

/**
 * Plugin Name:       Block: Navigation Menu
 * Description:       Provides a block for the output of a selected classic menu.
 * Requires at least: 5.9
 * Requires PHP:      8.0
 * Version:           1.0.0
 * Author:            Say Hello GmbH
 * Author URI:        https://www.sayhello.ch/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shb-nav-menu
 */

function shb_nav_menu_block_init()
{
	register_block_type(__DIR__ . '/build', [
		'render_callback' => 'shb_nav_menu_render_block',
		'attributes' => [
			'menu_position' => [
				'type'  => 'string',
			]
		],
	]);
}
add_action('init', 'shb_nav_menu_block_init');

function shb_nav_menu_render_block(array $attributes, string $content, WP_Block $block)
{

	$classNameBase = wp_get_block_default_classname($block->name);

	if (empty($menu_position = $attributes['menu_position'] ?? '')) {
		ob_start();
?>
		<div class="c-editormessage c-editormessage--error"><?php _ex('Bitte wählen Sie eine vordefinierte Navigation aus.', 'Menu block editor message', 'shb-nav-menu'); ?></div>
	<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	ob_start();

	?>
	<div <?php echo get_block_wrapper_attributes(['class' => "{$classNameBase}--{$menu_position}"]); ?>>

		<?php
		$menu = wp_nav_menu(
			[
				'echo' => false,
				'theme_location' => $menu_position,
				'container' => 'nav',
				'container_class' => "{$classNameBase} {$classNameBase}--container {$classNameBase}--{$menu_position}",
				'menu_class' => "{$classNameBase} {$classNameBase}--menu {$classNameBase}--{$menu_position}",
			]
		);

		if (empty($menu)) {
		?>
			<div class="c-editormessage c-editormessage--error"><?php _ex('Das ausgewählte Menü ist leer.', 'Menu block editor message', 'shb-nav-menu'); ?></div>
		<?php
		} else {
			echo $menu;
		}
		?>
	</div>
<?php
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}


/**
 * Register custom REST API endpoints
 *
 * @return void
 */
function shb_nav_menu_endpoints()
{
	register_rest_route('shb', '/nav-menus', array(
		'methods' => 'GET',
		'permission_callback' => '__return_true',
		'callback' => function () {
			if (empty($nav_menus = wp_get_nav_menus())) {
				return new WP_REST_Response($nav_menus);
			}

			$response_data = [];

			foreach (array_values($nav_menus) as $values) {
				$response_data[] = [
					'slug' => $values->slug,
					'id' => $values->term_id,
					'title' => $values->name
				];
			}

			return new WP_REST_Response($response_data);
		},
	));

	register_rest_route('shb', 'nav-menu-positions', array(
		'methods' => 'GET',
		'permission_callback' => '__return_true',
		'callback' => function () {
			if (empty($nav_menus = get_registered_nav_menus())) {
				return new WP_REST_Response($nav_menus);
			}

			$response_data = [];

			foreach ($nav_menus as $key => $label) {
				$response_data[] = [
					'id' => $key,
					'title' => $label
				];
			}

			return new WP_REST_Response($response_data);
		},
	));
}
add_action('rest_api_init', 'shb_nav_menu_endpoints');

/**
 * Additional CSS class names for the menu entries
 *
 * @param array $classes
 * @param WP_Post $item
 * @param stdClass $args
 * @param string $depth
 * @return array
 */
function shb_nav_menu_class(array $classes, WP_Post $item, stdClass $args, string $depth)
{

	$classNameBase = wp_get_block_default_classname('shb/nav-menu');

	$classes[] = "{$classNameBase}__entry {$classNameBase}__entry--depth-{$depth} {$classNameBase}__entry--{$args->theme_location}";

	if ($item->current) {
		$classes[] = "{$classNameBase}__entry--current";
	}

	if ($item->current_item_ancestor) {
		$classes[] = "{$classNameBase}__entry--current_item_ancestor";
	}

	if ($item->current_item_parent) {
		$classes[] = "{$classNameBase}__entry--current_item_parent";
	}

	return (array) $classes;
}
add_filter('nav_menu_css_class', 'shb_nav_menu_class', 10, 4);


/**
 * Customise the nav menu function arguments
 *
 * @param array $args
 * @return array
 */
function shb_nav_menu_args(array $args)
{
	$classNameBase = wp_get_block_default_classname('shb/nav-menu');

	$args['fallback_cb'] = false;
	$args['menu_class'] = "{$classNameBase}__entries {$classNameBase}__entries--{$args['theme_location']}";

	return (array) $args;
}
add_filter('wp_nav_menu_args', 'shb_nav_menu_args', 10, 1);

/**
 * Additional menu link CSS class name
 *
 * @param array $atts
 * @return array
 */
function shb_nav_menu_link_attributes(array $atts)
{
	$classNameBase = wp_get_block_default_classname('shb/nav-menu');

	$atts['class'] = $atts['class'] ?? '';

	$class = explode(' ', $atts['class']);
	$class[] = "{$classNameBase}__entrylink";

	// Remove empty entries
	$class = array_filter(array_slice($class, 0));

	$atts['class'] = implode(' ', $class);

	return $atts;
}
add_filter('nav_menu_link_attributes', 'shb_nav_menu_link_attributes');
