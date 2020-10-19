// https://www.codeinwp.com/blog/plugin-options-page-gutenberg/
// https://github.com/HardeepAsrani/my-awesome-plugin/

import axios from 'axios';
// Settings Panels
import { Notice } from './Components/Notice';
import { Header } from './Components/Header';
import { AddressFieldPanel } from './Components/Panels/AddressFieldPanel';
import { CompletedPaymentPanel } from './Components/Panels/CompletedPaymentPanel';
import { EmailSettingsPanel } from './Components/Panels/EmailSettingsPanel';
import { TestEmailPanel } from './Components/Panels/TestEmailPanel';
import { MollieApiKeysPanel } from './Components/Panels/MollieApiKeysPanel';
import { MollieApiModePanel } from './Components/Panels/MollieApiModePanel';
import { CustomReturnPanel } from './Components/Panels/CustomReturnPanel';
import { PrivacyPolicyPanel } from './Components/Panels/PrivacyPolicyPanel';
import { EmailReceiptsPanel } from './Components/Panels/EmailReceiptsPanel';
import { DebugModePanel } from './Components/Panels/DebugModePanel';
import { ActionSchedulerPanel } from './Components/Panels/ActionSchedulerPanel';
import { ThemePanel } from "./Components/Panels/ThemePanel";
import { SubscriptionPanel } from "./Components/Panels/SubscriptionPanel"
import {Btn} from "./Components/Btn";

const { __ } = wp.i18n;

const {
	Panel,
	PanelRow,
	Spinner,
	TabPanel
} = wp.components;
const { Component, Fragment } = wp.element;
const { applyFilters } = wp.hooks;

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

		this.tabs = {};
	}

	componentDidMount() {
		if ( false === this.state.isAPILoaded ) {
			this.getSettings();
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
		// this.hideNotice();
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

	// Update all settings
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

	// Update an individual setting
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

	render() {

		// Show spinner if not yet loaded
		if ( ! this.state.isAPILoaded ) {
			return (
				<div className="kd-absolute kd-inset-0 kd-flex kd-items-center kd-justify-center">
					<Spinner />
				</div>
			)
		}

		// Define tabs and panels
		this.tabs = applyFilters('kudos.settings.registerTabs', [
			{
				name: 'mollie',
				title: __('Mollie', 'kudos-donations'),
				className: 'tab-mollie',
				content:
					<Fragment>
						<MollieApiModePanel
								{...this.state}
								mollieChanged={ this.mollieChanged }
								handleInputChange={ this.handleInputChange }
						/>
						<MollieApiKeysPanel
								{...this.state}
								mollieChanged={ this.mollieChanged }
								handleInputChange={ this.handleInputChange }
						/>
					</Fragment>
			},
			{
				name: 'customize',
				title: __('Customize', 'kudos-donations'),
				className: 'tab-customize',
				content:
					<Fragment>
						<ThemePanel
							{...this.state}
							handleInputChange={this.handleInputChange}
						/>
						<AddressFieldPanel
							{...this.state}
							handleInputChange={this.handleInputChange}
						/>
						<SubscriptionPanel
							{...this.state}
							handleInputChange={this.handleInputChange}
						/>
						<CompletedPaymentPanel
							{...this.state}
							handleInputChange={this.handleInputChange}
						/>
						<CustomReturnPanel
							{...this.state}
							handleInputChange={this.handleInputChange}
						/>
						<PrivacyPolicyPanel
							{...this.state}
							handleInputChange={this.handleInputChange}
						/>
					</Fragment>
			},
			{
				name: 'email',
				title: __('Email', 'kudos-donations'),
				className: 'tab-email',
				content:
					<Fragment>
						<EmailReceiptsPanel
							{...this.state}
							handleInputChange={this.handleInputChange}
						/>
						<EmailSettingsPanel
							{...this.state}
							handleInputChange={this.handleInputChange}
						/>
						<TestEmailPanel
							handleInputChange={this.handleInputChange}
							showNotice={this.showNotice}
						/>
					</Fragment>
			},
			{
				name: 'advanced',
				title: __('Advanced', 'kudos-donations'),
				className: 'tab-advanced',
				content:
					<Fragment>
						<ActionSchedulerPanel
							{...this.state}
							handleInputChange={this.handleInputChange}
						/>
						<DebugModePanel
							{...this.state}
							handleInputChange={this.handleInputChange}
						/>
					</Fragment>
			}

		], this.state, this.handleInputChange);

		return (

			<Fragment>

				<Notice
					showNotice={ this.state.showNotice }
					hideNotice={ this.hideNotice }
					message={this.state.noticeMessage}
				/>

				<Header
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
					tabs={
						Object.entries(this.tabs).map((tab) => {
							tab = tab[1];
							return tab;
						})
					}
				>
					{
						( tab ) => {

							return (

								<div className="kudos-settings-main dashboard-wrap kd-mx-auto kd-container">

									<Panel>
										{tab.content}
									</Panel>

									<PanelRow className={'kd-justify-center'}>
										<Btn
											isPrimary
											disabled={
												this.state.isSaving ||
												!this.state.isEdited
											}
											isBusy={
												this.state.isSaving ||
												this.state.checkingApi
											}
											onClick={this.updateAll}
										>
											{__('Save', 'kudos-donations')}
										</Btn>
									</PanelRow>
								</div>
							)
						}

					}

				</TabPanel>
			</Fragment>
		);
	}
}

export { KudosAdmin };
