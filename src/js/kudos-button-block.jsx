/**
 * Internal block libraries
 */

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const {
	PanelBody,
	PanelRow,
	ColorPalette,
	BaseControl,
	TextControl,
} = wp.components;
const {
	RichText,
	BlockControls,
	AlignmentToolbar,
	InspectorControls,
} = wp.blockEditor;

import logo from '../img/logo-colour.svg';

/**
 * Register block
 */
export default registerBlockType( 'iseardmedia/kudos-button', {
	// Block Title
	title: __( 'Kudos Button', 'kudos-donations' ),
	// Block Description
	description: __(
		'Adds a Kudos donate button to your post or page.',
		'kudos-donations'
	),
	// Block Category
	category: 'widgets',
	// Block Icon
	icon: <img width="30" src={ logo } alt="Kudos Logo" />,
	// Block Keywords
	keywords: [
		__( 'Kudos', 'kudos-donations' ),
		__( 'Button', 'kudos-donations' ),
		__( 'Donate', 'kudos-donations' ),
	],
	example: {
		attributes: {
			label: __( 'Donate now!', 'kudos-donations' ),
			alignment: 'center',
		},
	},
	// Defining the edit interface
	edit: ( props ) => {
		const {
			label,
			alignment,
			color,
			modalHeader,
			modalBody,
			buttonName,
		} = props.attributes;

		const colors = [
			{ name: 'orange', color: '#ff9f1c' },
			{ name: 'green', color: '#2ec4b6' },
		];

		const onChangeLabel = ( newLabel ) => {
			props.setAttributes( { label: newLabel } );
		};

		const onChangeAlignment = ( newAlignment ) => {
			props.setAttributes( {
				alignment: newAlignment === undefined ? 'none' : newAlignment,
			} );
		};

		const onChangeHeader = ( newHeader ) => {
			props.setAttributes( { modalHeader: newHeader } );
		};

		const onChangeBody = ( newBody ) => {
			props.setAttributes( { modalBody: newBody } );
		};

		const onChangeName = ( newName ) => {
			props.setAttributes( { buttonName: newName } );
		};

		const onChangeColor = ( newColor ) => {
			props.setAttributes( { color: newColor } );
		};

		return (
			<div>
				<InspectorControls>
					<PanelBody
						title={ __( 'Donation form', 'kudos-donations' ) }
						initialOpen={ true }
					>
						<PanelRow>
							<TextControl
								label={ __( 'Header text', 'kudos-donations' ) }
								type={ 'text' }
								value={ modalHeader }
								onChange={ onChangeHeader }
							/>
						</PanelRow>
						<PanelRow>
							<TextControl
								label={ __( 'Body text', 'kudos-donations' ) }
								type={ 'text' }
								value={ modalBody }
								onChange={ onChangeBody }
							/>
						</PanelRow>
					</PanelBody>
					<PanelBody
						title={ __( 'Button style', 'kudos-donations' ) }
						initialOpen={ true }
					>
						<PanelRow>
							<BaseControl
								label={ __( 'Background', 'kudos-donations' ) }
								id={ 'background' }
							>
								<ColorPalette
									colors={ colors }
									value={ color }
									onChange={ onChangeColor }
									disableCustomColors
									clearable={ false }
								/>
							</BaseControl>
						</PanelRow>
					</PanelBody>
					<PanelBody
						title={ __( 'Other', 'kudos-donations' ) }
						initialOpen={ true }
					>
						<PanelRow>
							<TextControl
								label={ __( 'Button name', 'kudos-donations' ) }
								type={ 'text' }
								value={ buttonName }
								onChange={ onChangeName }
							/>
						</PanelRow>
					</PanelBody>
				</InspectorControls>

				<BlockControls>
					<AlignmentToolbar
						value={ alignment }
						onChange={ onChangeAlignment }
					/>
				</BlockControls>

				<div
					className={
						props.className + ' has-text-align-' + alignment
					}
				>
					<RichText
						className={ 'kudos_button kudos_button_donate' }
						style={ { backgroundColor: color } }
						formattingControls={ [
							'bold',
							'italic',
							'text-color',
							'strikethrough',
						] }
						tagName="button"
						onChange={ onChangeLabel }
						value={ label }
					/>
				</div>
			</div>
		);
	},

	// Defining the front-end interface
	save: () => {
		return null;
	},
} );
