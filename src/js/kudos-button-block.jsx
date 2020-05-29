/**
 * Internal block libraries
 */


const { __ } = wp.i18n;

const { registerBlockType } = wp.blocks;

const { RichText, BlockControls, AlignmentToolbar, InspectorControls } = wp.blockEditor

import logo from "../img/logo-colour.svg"
import {KudosButton} from "./components/KudosButton"

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
    icon: <img width="30" src={logo} alt="Kudos Logo"/>,
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
            default: 'Donate now'
        },
        alignment: {
            type: 'string',
            default: 'none',
        },
        style: {
            type: 'string',
            default: 'style-green'
        }
    },
    example: {
        attributes: {
            content: __( 'Donate now!', 'kudos-donations' ),
            alignment: 'center',
        },
    },
    // Defining the edit interface
    edit: props => {
        const {
            attributes: { content, alignment, style },
            className,
        } = props;

        const onChangeContent = ( newContent ) => {
            props.setAttributes( { content: newContent } );
        };

        const onChangeAlignment = ( newAlignment ) => {
            props.setAttributes( {
                alignment: newAlignment === undefined ? 'none' : newAlignment,
            } );
        };

        return (
            <div>
                <InspectorControls>
                    <p>Hello poo</p>
                </InspectorControls>
                <BlockControls>
                    <AlignmentToolbar
                        value={ alignment }
                        onChange={ onChangeAlignment }
                    />
                </BlockControls>
                <div className={className + " has-text-align-" + alignment}>
                    <RichText
                        className={ "kudos_button kudos_button_donate " + style }
                        tagName="button"
                        onChange={ onChangeContent }
                        value={ content }
                    />
                </div>
            </div>
        );
    },
    // Defining the front-end interface
    save: props => {

        console.log(props)
        return (

            <KudosButton
                className={props.className}
                alignment={props.attributes.alignment}
                style={props.attributes.style}
                label={props.attributes.content}
            />
        );
    },
});