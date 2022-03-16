import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { registerBlockType } from "@wordpress/blocks";
import { PanelBody, SelectControl } from "@wordpress/components";
import { withSelect } from "@wordpress/data";
import ServerSideRender from "@wordpress/server-side-render";
import { __, _x } from "@wordpress/i18n";

// Stores to get/put data with REST API
import "./stores";

// Styles for the block editor
import "./editor.scss";

// This block is server-side rendered, so there's no save method here

const blockName = "shb/nav-menu";

registerBlockType(blockName, {
    edit: withSelect(select => {
        const menu_positions = select("shb/nav-menu-positions").getEntries();

        if (!menu_positions || !Object.keys(menu_positions).length) {
            return { menu_positions: [] };
        }

        let menu_position_selection = [
            {
                label: _x("Keine Auswahl", "Default selector label", "sha"),
                value: "",
            },
        ];

        menu_positions.map(menu => {
            menu_position_selection.push({ value: menu.id, label: menu.title });
        });

        return {
            menu_positions: menu_position_selection,
        };
    })(({ attributes, menu_positions, setAttributes }) => {
        const { menu_position } = attributes;
        const blockProps = useBlockProps();

        return [
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody
                        title={_x("Block-Optionen", "PanelBody Title", "sha")}
                        initialOpen={true}
                    >
                        <SelectControl
                            label={_x(
                                "Vordefinierte Navigation auswählen",
                                "Select control label",
                                "sha"
                            )}
                            value={menu_position}
                            onChange={menu_position => setAttributes({ menu_position })}
                            options={menu_positions}
                        />
                    </PanelBody>
                </InspectorControls>
                <ServerSideRender block={blockName} attributes={attributes} />
            </div>,
        ];
    }),
});
