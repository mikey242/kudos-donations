/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { Component } = wp.element;
const { registerBlockType } = wp.blocks;
const {
	Button,
	PanelBody,
	TextControl,
	RadioControl,
	SelectControl,
	Spinner
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
	edit: class extends Component {

		constructor() {
			super();
			this.onChangeButtonLabel = this.onChangeButtonLabel.bind(this);
			this.onChangeAlignment = this.onChangeAlignment.bind(this);
			this.onChangeHeader = this.onChangeHeader.bind(this);
			this.onChangeBody = this.onChangeBody.bind(this);
			this.onChangeCampaignLabel = this.onChangeCampaignLabel.bind(this);
			this.onChangeAmountType = this.onChangeAmountType.bind(this);
			this.onChangeFixedAmounts = this.onChangeFixedAmounts.bind(this);
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

		addCampaignLabel(label) {
			let current = this.state.settings._kudos_campaign_labels;
			// this.updateSetting('_kudos_campaign_labels', _.union(current, [ label ]));
			let combined = _.union(current,[{
				date: new Date(),
				label: label
			}]);
			this.updateSetting('_kudos_campaign_labels', _.uniq(combined, 'label') );
			this.onChangeCampaignLabel(label);
		};

		onChangeButtonLabel( newValue ) {
			this.props.setAttributes( { button_label: newValue } );
		};

		onChangeAlignment( newValue ) {
			this.props.setAttributes( {
				alignment: newValue === undefined ? 'none' : newValue,
			} );
		};

		onChangeHeader( newValue ) {
			this.props.setAttributes( { modal_title: newValue } );
		};

		onChangeBody( newValue ) {
			this.props.setAttributes( { welcome_text: newValue } );
		};

		onChangeCampaignLabel( newValue ) {
			this.props.setAttributes( { campaign_label: newValue } );
		};

		onChangeNewCampaignLabel( newValue ) {
			this.props.setAttributes( { new_campaign_label: newValue } );
		};

		onChangeAmountType( newValue ) {
			this.props.setAttributes( { amount_type: newValue } );
		};

		onChangeFixedAmounts( newValue ) {
			this.props.setAttributes( { fixed_amounts: newValue } );
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
							title={ __( 'Modal (pop-up)', 'kudos-donations' ) }
							initialOpen={ false }
						>
							<TextControl
								label={ __( 'Header', 'kudos-donations' ) }
								type={ 'text' }
								value={ this.props.attributes.modal_title }
								onChange={ this.onChangeHeader }
							/>

							<TextControl
								label={ __( 'Welcome text', 'kudos-donations' ) }
								type={ 'text' }
								value={ this.props.attributes.welcome_text }
								onChange={ this.onChangeBody }
							/>

						</PanelBody>

						<PanelBody
							title={ __( 'Donation amount', 'kudos-donations' ) }
							initialOpen={ false }
						>
							<RadioControl
								label={ __( 'Type', 'kudos-donations' ) }
								help={__("The type of donation amount available", 'kudos-donations')}
								selected={ this.props.attributes.amount_type }
								options={ [
									{ label: 'Open', value: 'open' },
									{ label: 'Fixed', value: 'fixed' },
								] }
								onChange={ this.onChangeAmountType }
							/>

							{ this.props.attributes.amount_type !== 'open' ?

								<Fragment>
									<TextControl
										label={ __(	'Amounts',	'kudos-donations' ) + ':' }
										help={ __( 'Enter a comma separated list of values to use.', 'kudos-donations' ) }
										value={ this.props.attributes.fixed_amounts }
										onChange={ this.onChangeFixedAmounts }
									/>
								</Fragment>

								: '' }

						</PanelBody>

						<PanelBody
							title={ __( 'Campaign', 'kudos-donations' ) }
							initialOpen={ false }
						>

							<SelectControl
								label={ __( 'Campaign label', 'kudos-donations' ) }
								help={__('Select an existing campaign label so you can identify it on the transactions page', 'kudos-donations')}
								value={ this.props.attributes.campaign_label }
								onChange={ this.onChangeCampaignLabel }
								options={
									Object.values(this.state.settings._kudos_campaign_labels).map((value) => {
										return {
											'label': value.label,
											'value': value.label
										}
									})
								}
							/>

							<TextControl
								label={ __(
									'Add campaign',
									'kudos-donations'
								) }
								id={'kudos_new_campaign'}
								className={'kd-inline'}
								type={ 'text' }
								value={ this.state.newLabel }
								onChange={ (newLabel) => this.setState({newLabel}) }
							/>

							<Button
								label={ __('Add campaign', 'kudos-donations') }
								isSecondary
								isSmall
								onClick={
									() => this.addCampaignLabel(document.getElementById('kudos_new_campaign').value)
								}
							>{__('Add campaign', 'kudos-donations')}</Button>
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
							className={ 'kd-transition kd-duration-150 kd-ease-in-out focus:kd-shadow-focus focus:kd-outline-none kd-font-sans kd-text-center kd-text-white kd-leading-normal kd-font-normal kd-normal-case kd-no-underline kd-w-auto kd-h-auto kd-inline-flex kd-items-center kd-select-none kd-py-3 kd-px-5 kd-m-1 kd-rounded-lg kd-cursor-pointer kd-shadow-none kd-border-none kd-bg-theme hover:kd-bg-theme-dark kudos_button_donate' }
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
	save: () => {
		return null;
	},
} );
