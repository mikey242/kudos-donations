/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const {
	PanelBody,
	TextControl,
	RadioControl,
} = wp.components;
const {
	RichText,
	BlockControls,
	AlignmentToolbar,
	InspectorControls,
} = wp.blockEditor;

const { Fragment } = wp.element;

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
			button_label,
			alignment,
			modal_header,
			welcome_text,
			donation_label,
			amount_type,
			fixed_amounts,
		} = props.attributes;

		const onChangeButtonLabel = ( newValue ) => {
			props.setAttributes( { button_label: newValue } );
		};

		const onChangeAlignment = ( newValue ) => {
			props.setAttributes( {
				alignment: newValue === undefined ? 'none' : newValue,
			} );
		};

		const onChangeHeader = ( newValue ) => {
			props.setAttributes( { modal_header: newValue } );
		};

		const onChangeBody = ( newValue ) => {
			props.setAttributes( { welcome_text: newValue } );
		};

		const onChangeDonationLabel = ( newValue ) => {
			props.setAttributes( { donation_label: newValue } );
		};

		const onChangeAmountType = ( newValue ) => {
			props.setAttributes( { amount_type: newValue } );
		};

		const onChangeFixedAmounts = ( newValue ) => {
			props.setAttributes( { fixed_amounts: newValue } );
		};

		return (
			<div>
				<InspectorControls>

					<PanelBody
						title={ __( 'Modal (pop-up)', 'kudos-donations' ) }
						initialOpen={ false }
					>
						<TextControl
							label={ __( 'Header', 'kudos-donations' ) }
							type={ 'text' }
							value={ modal_header }
							onChange={ onChangeHeader }
						/>

						<TextControl
							label={ __( 'Welcome text', 'kudos-donations' ) }
							type={ 'text' }
							value={ welcome_text }
							onChange={ onChangeBody }
						/>

					</PanelBody>

					<PanelBody
						title={ __( 'Donation amount', 'kudos-donations' ) }
						initialOpen={ false }
					>
						<RadioControl
							label={ __( 'Type', 'kudos-donations' ) }
							help={__("The type of donation amount available", 'kudos-donations')}
							selected={ amount_type }
							options={ [
								{ label: 'Open', value: 'open' },
								{ label: 'Fixed', value: 'fixed' },
							] }
							onChange={ onChangeAmountType }
						/>

						{ amount_type !== 'open'
							? [

						<Fragment>
							<TextControl
								label={ __(	'Amounts:',	'kudos-donations' ) }
								help={ __( 'Enter a comma separated list of values to use.', 'kudos-donations' ) }
								value={ fixed_amounts }
								onChange={ onChangeFixedAmounts }
							/>
						</Fragment>

							]
						: '' }

					</PanelBody>

					<PanelBody
						title={ __( 'Other', 'kudos-donations' ) }
						initialOpen={ false }
					>
						<TextControl
							label={ __(
								'Donation label',
								'kudos-donations'
							) }
							type={ 'text' }
							value={ donation_label }
							onChange={ onChangeDonationLabel }
						/>
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
						style={ { backgroundColor: kudos.theme_color } }
						formattingControls={ [
							'bold',
							'italic',
							'text-color',
							'strikethrough',
						] }
						tagName="button"
						onChange={ onChangeButtonLabel }
						value={ button_label }
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
