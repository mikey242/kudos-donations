import axios from 'axios';
// Settings Panels
import { DonationFormPanel } from './Components/Panels/DonationFormPanel';
import { CompletedPaymentPopup } from './Components/Panels/CompletedPaymentPopup';
import { KudosNotice } from './Components/KudosNotice';
import { KudosHeader } from './Components/KudosHeader';
import { EmailSettingsPanel } from './Components/Panels/EmailSettingsPanel';
import { TestEmailPanel } from './Components/Panels/TestEmailPanel';
import { MolliePanel } from './Components/Panels/MolliePanel';
import { DonationButtonPanel } from './Components/Panels/DonationButtonPanel';
import { CustomReturnPanel } from './Components/Panels/CustomReturnPanel';
import { AVGPanel } from './Components/Panels/AVGPanel';
import { EmailReceiptsPanel } from './Components/Panels/EmailReceiptsPanel';
import { InvoiceCompanyPanel } from './Components/Panels/InvoiceCompanyPanel';
import { GenerateInvoicesPanel } from './Components/Panels/GenerateInvoices';
import { PrimaryButton } from './Components/FormElements/PrimaryButton';
import { DiagnosticsPanel } from './Components/Panels/DiagnosticsPanel';

const { __ } = wp.i18n;

const { Placeholder, Spinner, TabPanel } = wp.components;

const { Component, Fragment } = wp.element;

function getTabName() {
	const searchParams = new URLSearchParams( window.location.search );
	if ( searchParams.has( 'tabName' ) ) {
		return searchParams.get( 'tabName' );
	}
	return 'mollie';
}

class KudosAdmin extends Component {
	constructor() {
		super( ...arguments );

		this.changeTab = this.changeTab.bind( this );
		this.updateSetting = this.updateSetting.bind( this );
		this.updateAll = this.updateAll.bind( this );
		this.handleInputChange = this.handleInputChange.bind( this );
		this.mollieChanged = this.mollieChanged.bind( this );
		this.showNotice = this.showNotice.bind( this );
		this.hideNotice = this.hideNotice.bind( this );
		this.checkApiKey = this.checkApiKey.bind( this );

		this.state = {
			tabName: getTabName(),
			showNotice: false,
			noticeMessage: '',
			isMollieEdited: false,
			isEdited: false,
			isAPILoaded: false,
			isAPISaving: false,
			checkingApi: false,
			settings: {},
		};
	}

	componentDidMount() {
		if ( false === this.state.isAPILoaded ) {
			this.getSettings();
			this.getDiagnostics();
		}
	}

	mollieChanged() {
		this.setState( {
			isMollieEdited: true,
			settings: {
				_kudos_mollie_connected: false,
			},
		} );
	}

	changeTab() {
		this.getSettings();
	}

	checkApiKey() {
		this.setState( {
			checkingApi: true,
			isAPISaving: true,
		} );

		// Create form data from current state
		// eslint-disable-next-line no-undef
		const formData = new FormData();
		formData.append( 'action', 'check_mollie_connection' );
		formData.append(
			'apiMode',
			this.state.settings._kudos_mollie_api_mode
		);
		formData.append(
			'testKey',
			this.state.settings._kudos_mollie_test_api_key
		);
		formData.append(
			'liveKey',
			this.state.settings._kudos_mollie_live_api_key
		);

		// Perform Get request
		axios
			.get( window.kudos.checkApiUrl, {
				headers: {
					// eslint-disable-next-line no-undef
					'X-WP-Nonce': wpApiSettings.nonce,
				},
				params: {
					apiMode: this.state.settings._kudos_mollie_api_mode,
					testKey: this.state.settings._kudos_mollie_test_api_key,
					liveKey: this.state.settings._kudos_mollie_live_api_key,
				},
			} )
			.then( ( response ) => {
				this.showNotice( response.data.data );
				this.setState( {
					settings: {
						...this.state.settings,
						_kudos_mollie_connected: response.data.success,
					},
					checkingApi: false,
					isAPISaving: false,
				} );
			} );
		// .catch( ( error ) => {
		// 	console.log( error );
		// } );
	}

	getDiagnostics() {
		axios
			.get( window.kudos.getDiagnosticsUrl, {
				headers: {
					// eslint-disable-next-line no-undef
					'X-WP-Nonce': wpApiSettings.nonce,
				},
			} )
			.then( ( response ) => {
				if ( response.data.success ) {
					this.setState( {
						diagnostics: {
							...response.data.data,
						},
						checkingApi: false,
						isAPISaving: false,
					} );
				}
			} );
		// .catch( ( error ) => {
		// 	console.log( error );
		// } );
	}

	handleInputChange( option, value ) {
		this.setState( {
			isEdited: true,
			settings: {
				...this.state.settings,
				[ option ]: value,
			},
		} );
	}

	showNotice( message ) {
		this.hideNotice();
		this.setState( {
			showNotice: true,
			noticeMessage: message,
		} );
	}

	hideNotice() {
		this.setState( {
			showNotice: false,
		} );
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

	updateAll( showNotice = true ) {
		this.setState( { isAPISaving: true } );

		// Delete empty settings keys
		for ( const key in this.state.settings ) {
			if ( this.state.settings[ key ] === null ) {
				delete this.state.settings[ key ];
			}
		}

		//Create WordPress settings model
		const model = new wp.api.models.Settings( {
			...this.state.settings,
		} );

		//Save to database
		model
			.save()
			.then( ( response ) => {
				// Commit state
				this.setState( {
					settings: {
						...response,
					},
					isEdited: false,
					isAPISaving: false,
				} );
				if ( showNotice ) {
					this.showNotice(
						__( 'Setting(s) updated', 'kudos-donations' )
					);
				}
				if ( this.state.isMollieEdited ) {
					this.checkApiKey();
					this.setState( {
						isMollieEdited: false,
					} );
				}
			} )
			.fail( ( response ) => {
				if ( showNotice ) {
					this.showNotice( response.statusText );
				}
			} );
	}

	updateSetting( option, value ) {
		this.setState( { isAPISaving: true } );

		//Create WordPress settings model
		const model = new wp.api.models.Settings( {
			[ option ]: value,
		} );

		//Save to database
		model.save().then( ( response ) => {
			// Commit state
			this.setState( {
				[ option ]: response[ option ],
				isAPISaving: false,
			} );
		} );
	}

	renderTab( tab ) {
		switch ( tab.name ) {
			case 'mollie':
				return (
					<MolliePanel
						{ ...this.state }
						mollieChanged={ this.mollieChanged }
						handleInputChange={ this.handleInputChange }
					/>
				);

			case 'customize':
				return (
					<Fragment>
						<DonationButtonPanel
							{ ...this.state }
							handleInputChange={ this.handleInputChange }
						/>
						<DonationFormPanel
							{ ...this.state }
							handleInputChange={ this.handleInputChange }
						/>
						<CompletedPaymentPopup
							{ ...this.state }
							handleInputChange={ this.handleInputChange }
						/>
						<CustomReturnPanel
							{ ...this.state }
							handleInputChange={ this.handleInputChange }
						/>
						<AVGPanel
							{ ...this.state }
							handleInputChange={ this.handleInputChange }
						/>
					</Fragment>
				);

			case 'email':
				return (
					<Fragment>
						<EmailReceiptsPanel
							{ ...this.state }
							handleInputChange={ this.handleInputChange }
						/>
						<EmailSettingsPanel
							{ ...this.state }
							handleInputChange={ this.handleInputChange }
						/>
						<TestEmailPanel
							handleInputChange={ this.handleInputChange }
							showNotice={ this.showNotice }
						/>
					</Fragment>
				);

			case 'invoice':
				return (
					<Fragment>
						<GenerateInvoicesPanel
							{ ...this.state }
							handleInputChange={ this.handleInputChange }
						/>
						<InvoiceCompanyPanel
							{ ...this.state }
							handleInputChange={ this.handleInputChange }
						/>
					</Fragment>
				);

			case 'advanced':
				return (
					<Fragment>
						<DiagnosticsPanel { ...this.state.diagnostics } />
					</Fragment>
				);
		}
	}

	render() {
		if ( ! this.state.isAPILoaded ) {
			return (
				<Placeholder key="kudos-loader">
					<Spinner />
				</Placeholder>
			);
		}
		return (
			<Fragment>
				<KudosNotice
					id={ 'kudos-notice-' + this.state.noticeCount }
					showNotice={ this.state.showNotice }
					hideNotice={ this.hideNotice }
				>
					<p>{ this.state.noticeMessage }</p>
				</KudosNotice>

				<KudosHeader
					apiConnected={ this.state.settings._kudos_mollie_connected }
					apiMode={ this.state.settings._kudos_mollie_api_mode }
					isMollieEdited={ this.state.isMollieEdited }
					checkingApi={ this.state.checkingApi }
				/>

				<TabPanel
					className="kudos-tab-panel"
					onSelect={ ( tab ) => {
						this.changeTab( tab );
					} }
					activeClass="is-active"
					initialTabName={ this.state.tabName }
					tabs={ [
						{
							name: 'mollie',
							title: 'Mollie',
							className: 'tab-mollie',
						},
						{
							name: 'customize',
							title: __( 'Customize', 'kudos-donations' ),
							className: 'tab-customize',
						},
						{
							name: 'email',
							title: __( 'Email', 'kudos-donations' ),
							className: 'tab-email',
						},
						{
							name: 'invoice',
							title: __( 'Invoice', 'kudos-donations' ),
							className: 'tab-Invoice',
						},
						{
							name: 'advanced',
							title: __( 'Advanced', 'kudos-donations' ),
							className: 'tab-advanced',
						},
					] }
				>
					{ ( tab ) => {
						return (
							<div
								className="kudos-settings-main dashboard-wrap"
								key="kudos-settings"
							>
								{ this.renderTab( tab ) }
								<PrimaryButton
									className={ 'justify-center' }
									label="Save"
									disabled={
										this.state.isSaving ||
										! this.state.isEdited
									}
									isBusy={
										this.state.isSaving ||
										this.state.checkingApi
									}
									onClick={ this.updateAll }
								/>
							</div>
						);
					} }
				</TabPanel>
			</Fragment>
		);
	}
}

export { KudosAdmin };
