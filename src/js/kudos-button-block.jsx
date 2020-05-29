/**
 * Internal block libraries
 */

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const {Component} = wp.element;
const {PanelBody, PanelRow, ColorPalette, BaseControl, TextControl} = wp.components;
const { RichText, BlockControls, AlignmentToolbar, InspectorControls } = wp.blockEditor

import logo from "../img/logo-colour.svg"
import { KudosButton } from "./components/KudosButton"

/**
 * Register block
 */

export default registerBlockType( 'iseardmedia/kudos-button', {

    // Block Title
    title: __( 'Kudos Button', 'kudos-donations' ),
    // Block Description
    description: __('Adds a Kudos donate button to your post or page.', 'kudos-donations'),
    // Block Category
    category: 'widgets',
    // Block Icon
    icon: <img width="30" src={ logo } alt="Kudos Logo"/>,
    // Block Keywords
    keywords: [
        __( 'Kudos', 'kudos-donations' ),
        __( 'Button', 'kudos-donations' ),
        __( 'Donate', 'kudos-donations' ),
    ],
    attributes: {
        content: {
            type: 'array',
            source: 'children',
            selector: 'button',
            default: __('Donate now!', 'kudos-donations')
        },
        alignment: {
            type: 'string',
            default: 'none',
        },
        color: {
            type: 'string',
            default: '#ff9f1c'
        },
        modalHeader: {
            type: 'string',
            default: __('Support us!', 'kudos-donations')
        },
        modalBody: {
            type: 'string',
            default: __('Thank you for your donation. We appreciate your support!', 'kudos-donations')
        },
    },
    example: {
        attributes: {
            content: __( 'Donate now!', 'kudos-donations' ),
            alignment: 'center',
        },
    },
    // Defining the edit interface
    edit: class extends Component {

        //standard constructor for a component
        constructor() {
            super(...arguments);

            // example how to bind `this` to the current component for our callbacks
            this.onChangeContent = this.onChangeContent.bind(this);
            this.onChangeAlignment = this.onChangeAlignment.bind(this);
            this.onChangeHeader = this.onChangeHeader.bind(this);
            this.onChangeBody = this.onChangeBody.bind(this);
            this.onChangeColor = this.onChangeColor.bind(this);

            // some place for your state
            this.state = {};
        }

        componentDidMount() {
            wp.api.loadPromise.then( () => {
                let settings = new wp.api.models.Settings();
                settings.fetch().then( response => {
                    this.props.setAttributes( {
                        content: (this.props.attributes.content ?? response._kudos_button_label),
                        modalHeader: (this.props.attributes.modalHeader ?? response._kudos_form_header),
                        modalBody: (this.props.attributes.modalBody ?? response._kudos_form_text)
                    } );
                });
            });
        }

        onChangeContent( newContent ) {
            this.props.setAttributes( { content: newContent } );
        };

        onChangeAlignment( newAlignment ) {
            this.props.setAttributes( {
                alignment: newAlignment === undefined ? 'none' : newAlignment,
            } );
        };

        onChangeHeader(newHeader) {
            this.props.setAttributes({ modalHeader: newHeader })
        };

        onChangeBody(newBody) {
            this.props.setAttributes({ modalBody: newBody })
        };

        onChangeColor(newColor) {
            this.props.setAttributes({ color: newColor })
        };

        render() {

            const {
                attributes: { content, alignment, color, modalHeader, modalBody },
                className,
            } = this.props;

            const colors = [
                { name: 'orange', color: '#ff9f1c' },
                { name: 'green', color: '#2ec4b6' }
            ];

            return (
                <div>
                    <InspectorControls>
                        <PanelBody
                            title={__('Donation form', 'kudos-donations')}
                            initialOpen={true}
                        >
                            <PanelRow>
                                <TextControl
                                    label={__("Header text", 'kudos-donations')}
                                    type={"text"}
                                    value={modalHeader}
                                    onChange={this.onChangeHeader}
                                />
                            </PanelRow>
                            <PanelRow>
                                <TextControl
                                    label={__("Body text", 'kudos-donations')}
                                    type={"text"}
                                    value={modalBody}
                                    onChange={this.onChangeBody}
                                />
                            </PanelRow>
                        </PanelBody>
                        <PanelBody
                            title={__('Button style', 'kudos-donations')}
                            initialOpen={true}
                        >
                            <PanelRow>
                                <BaseControl
                                    label={__("Background", 'kudos-donations')}
                                >
                                    <ColorPalette
                                        colors={colors}
                                        onChange={this.onChangeColor}
                                        disableCustomColors
                                        clearable={false}
                                    />
                                </BaseControl>
                            </PanelRow>
                        </PanelBody>
                    </InspectorControls>

                    <BlockControls>
                        <AlignmentToolbar
                            value={alignment}
                            onChange={this.onChangeAlignment}
                        />
                    </BlockControls>

                    <div className={className + " has-text-align-" + alignment}>
                        <RichText
                            className={"kudos_button kudos_button_donate " + color}
                            style={{backgroundColor: color}}
                            tagName="button"
                            onChange={this.onChangeContent}
                            value={content}
                        />
                    </div>
                </div>
            )
        }
    },

    // Defining the front-end interface
    save: props => {
        return (
            <KudosButton
                className={props.className}
                alignment={props.attributes.alignment}
                style={props.attributes.color}
                label={props.attributes.content}
                header={props.attributes.modalHeader}
                body={props.attributes.modalBody}
            />
        );
    },
});