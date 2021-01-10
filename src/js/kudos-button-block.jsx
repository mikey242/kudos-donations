/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { Component } = wp.element;
const { registerBlockType } = wp.blocks;
const {
	PanelBody,
	SelectControl,
	Spinner
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
	edit: class extends Component {

		constructor() {
			super();
			this.onChangeButtonLabel = this.onChangeButtonLabel.bind(this);
			this.onChangeAlignment = this.onChangeAlignment.bind(this);
			this.onChangeCampaignLabel = this.onChangeCampaignLabel.bind(this);
			this.state = {
				settings: {},
				isAPILoaded: false,
			};
		}

		componentDidMount() {
			if ( false === this.state.isAPILoaded ) {
				this.getSettings();
			}
		}

		getSettings() {
			wp.api.loadPromise.then( () => {
				this.settings = new wp.api.models.Settings();
				this.settings.fetch().then( ( response ) => {
					this.setState( {
						settings: { ...response },
						isAPILoaded: true,
						showNotice: false,
					} );
				} );
			} );
		}

		// Update an individual setting
		updateSetting( option, value ) {

			//Create WordPress settings model
			const model = new wp.api.models.Settings( {
				[ option ]: value,
			} );

			//Save to database
			model.save().then( ( response ) => {
				// Commit state
				this.setState( {
					settings: {
						[ option ]: response[ option ]
					},
				} );
			} );
		}

		onChangeButtonLabel( newValue ) {
			this.props.setAttributes( { button_label: newValue } );
		};

		onChangeAlignment( newValue ) {
			this.props.setAttributes( {
				alignment: newValue === undefined ? 'none' : newValue,
			} );
		};

		onChangeCampaignLabel( newValue ) {
			this.props.setAttributes( { campaign_id: newValue } );
		};

		render() {

			// Show spinner if not yet loaded
			if ( ! this.state.isAPILoaded ) {
				return (
					<Spinner />
				)
			}

			return (

				<div>
					<InspectorControls>

						<PanelBody
							title={ __( 'Campaign', 'kudos-donations' ) }
							initialOpen={ false }
						>

							<SelectControl
								label={ __( 'Select campaign', 'kudos-donations' ) }
								help={__('Select your donation form', 'kudos-donations')}
								value={ this.props.attributes.campaign_id || 'default' }
								onChange={ this.onChangeCampaignLabel }
								options={
									Object.values(this.state.settings._kudos_campaigns).map( value => {
										return {
											'label': value.name,
											'value': value.slug
										}
									})
								}
							/>

						</PanelBody>
					</InspectorControls>

					<BlockControls>
						<AlignmentToolbar
							value={ this.props.attributes.alignment }
							onChange={ this.onChangeAlignment }
						/>
					</BlockControls>

					<div
						className={
							this.props.attributes.className + ' has-text-align-' + this.props.attributes.alignment
						}
					>
						<RichText
							className={ 'kd-transition kd-duration-150 kd-ease-in-out focus:kd-shadow-focus focus:kd-outline-none kd-font-sans kd-text-center kd-text-white kd-leading-normal kd-text-base kd-font-normal kd-normal-case kd-no-underline kd-w-auto kd-h-auto kd-inline-flex kd-items-center kd-select-none kd-py-3 kd-px-5 kd-m-1 kd-rounded-lg kd-cursor-pointer kd-shadow-none kd-border-none kd-bg-theme hover:kd-bg-theme-dark kudos_button_donate' }
							style={ { backgroundColor: kudos.theme_color } }
							formattingControls={ [
								'bold',
								'italic',
								'text-color',
								'strikethrough',
							] }
							tagName="button"
							onChange={ this.onChangeButtonLabel }
							value={ this.props.attributes.button_label }
						/>
					</div>
				</div>
			);
		}
	},

	// Defining the front-end interface
	save: (e) => {
		return null;
	},
} );
