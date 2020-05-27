import axios from "axios"
// Settings Panels
import {DonationFormPanel} from "./Panels/DonationFormPanel"
import {KudosNotice} from "./KudosNotice"
import {KudosHeader} from "./KudosHeader"
import {EmailSettingsPanel} from "./Panels/EmailSettingsPanel"
import {TestEmailPanel} from "./Panels/TestEmailPanel"
import {MolliePanel} from "./Panels/MolliePanel"
import {DonationButtonPanel} from "./Panels/DonationButtonPanel"
import {CompletedPaymentPanel} from "./Panels/CompletedPaymentPanel"

const { __ } = wp.i18n;

const {
    Placeholder,
    Spinner,
    TabPanel,
} = wp.components;

const {
    Component,
    Fragment,
} = wp.element;

class KudosAdmin extends Component {
    constructor() {

        super( ...arguments );

        this.changeTab = this.changeTab.bind(this);
        this.updateSetting = this.updateSetting.bind( this );
        this.handleInputChange = this.handleInputChange.bind(this);
        this.handleRadioChange = this.handleRadioChange.bind(this);
        this.showNotice = this.showNotice.bind(this);
        this.hideNotice = this.hideNotice.bind(this);
        this.checkApiKey = this.checkApiKey.bind(this);

        this.state = {
            showNotice: false,
            noticeMessage: '',
            isMollieConnected: false,
            isAPILoaded: false,
            isAPISaving: false,
            checkingApi: false,
            _kudos_mollie_api_mode: '',
            _kudos_mollie_test_api_key: '',
            _kudos_mollie_live_api_key: '',
            _kudos_mollie_connected: false,
            _kudos_smtp_enable: false,
            _kudos_smtp_host: '',
            _kudos_smtp_autotls: ''
        };

    }

    componentDidMount() {
        this.settings = new wp.api.models.Settings();
        if ( false === this.state.isAPILoaded ) {
            this.getSettings();
        }
    }

    changeTab(tab) {
        this.getSettings();
    }

    getSettings() {
        wp.api.loadPromise.then( () => {
            this.settings.fetch().then( response => {
                this.setState({
                    ...response,
                    isAPILoaded: true,
                    showNotice: false
                });
            });

        });
    }

    checkApiKey() {
        this.setState({
            checkingApi: true,
            isAPISaving: true
        });

        // Create form data from current state
        const formData = new FormData();
        formData.append('action', 'check_mollie_connection');
        formData.append('apiMode', this.state._kudos_mollie_api_mode);
        formData.append('testKey', this.state._kudos_mollie_test_api_key);
        formData.append('liveKey', this.state._kudos_mollie_live_api_key);

        // Perform Get request
        axios.get(kudos.checkApiUrl, {
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce
            },
            params: {
                'apiMode': this.state._kudos_mollie_api_mode,
                'testKey': this.state._kudos_mollie_test_api_key,
                'liveKey': this.state._kudos_mollie_live_api_key
            }
        }).then(response => {
            this.showNotice(response.data.data);
            this.setState({
                _kudos_mollie_connected: (response.data.success),
                checkingApi: false,
                isAPISaving: false
            })

        }).catch(error => {
            console.log(error)
        })
    }

    handleInputChange(e) {
        this.setState({
            ...this.state,
            [e.target.id]: e.target.value
        })
    }

    handleRadioChange(option, value) {
        this.setState({
            ...this.state,
            [option]: value
        })
    }

    showNotice(message) {
        this.hideNotice();
        this.setState({
            showNotice: true,
            noticeMessage: message,
        })
    }

    hideNotice() {
        this.setState({
            showNotice: false
        })
    }

    updateSetting( option, value, showNotice=true) {

        this.setState({ isAPISaving: true });

        //Create WordPress settings model
        const model = new wp.api.models.Settings(
            {
                [option]: value
            }
        );

        //Save to database
        model.save().then( response => {
            // Commit state
            this.setState({
                [option]: response[option],
                isAPISaving: false,
            });
            if (showNotice) {
                this.showNotice(__('Setting(s) updated', 'kudos-donations'));
            }
        });
    }

    render() {

        if(!this.state.isAPILoaded) {
            return (
                <Placeholder key='kudos-loader'>
                    <Spinner/>
                </Placeholder>
            )
        } else {
            return (
                <Fragment>

                    <KudosNotice id={'kudos-notice-' + this.state.noticeCount} showNotice={this.state.showNotice} hideNotice={this.hideNotice}>
                        <p>{this.state.noticeMessage}</p>
                    </KudosNotice>

                    <KudosHeader
                        apiConnected={this.state._kudos_mollie_connected}
                        checkingApi={this.state.checkingApi}
                    />

                    <TabPanel
                        className="kudos-tab-panel"
                        onSelect={(tab)=>{
                            this.changeTab(tab);
                        }}
                        activeClass="is-active"
                        initialTabName="mollie"
                        tabs={[
                            {
                                name: 'mollie',
                                title: 'Mollie',
                                className: 'tab-mollie',
                            },
                            {
                                name: 'customize',
                                title: __('Customize', 'kudos-donations'),
                                className: 'tab-customize',
                            },
                            {
                                name: 'receipts',
                                title: __('Receipts', 'kudos-donations'),
                                className: 'tab-receipts',
                            },
                            {
                                name: 'email',
                                title: __('Email', 'kudos-donations'),
                                className: 'tab-email',
                            },
                        ]}
                    >
                        {
                            (tab) => {
                                switch (tab.name) {

                                    case 'mollie':

                                        return (
                                            <div className="kudos-settings-main dashboard-wrap" key='kudos-settings'>
                                                <MolliePanel
                                                    {...this.state}
                                                    handleInputChange={this.handleInputChange}
                                                    updateSetting={this.updateSetting}
                                                    checkApiKey={this.checkApiKey}
                                                />
                                            </div>
                                        )

                                    case 'customize':

                                        return (
                                            <div className="kudos-settings-main dashboard-wrap" key='kudos-settings'>
                                                <DonationButtonPanel
                                                    {...this.state}
                                                    handleInputChange={this.handleInputChange}
                                                    updateSetting={this.updateSetting}
                                                />
                                                <DonationFormPanel
                                                    {...this.state}
                                                    handleInputChange={this.handleInputChange}
                                                    updateSetting={this.updateSetting}
                                                />
                                                <CompletedPaymentPanel
                                                    {...this.state}
                                                    handleInputChange={this.handleInputChange}
                                                    updateSetting={this.updateSetting}
                                                />
                                            </div>
                                        )

                                    case 'receipts':
                                        return (
                                            <div className="kudos-settings-main dashboard-wrap" key='kudos-settings'>
                                            </div>
                                        )

                                    case 'email':

                                        return (
                                            <div className="kudos-settings-main dashboard-wrap" key='kudos-settings'>
                                                <EmailSettingsPanel
                                                    {...this.state}
                                                    isSaving = {this.state.isAPISaving}
                                                    enableSmtp={this.state._kudos_smtp_enable}
                                                    handleInputChange={this.handleInputChange}
                                                    handleRadioChange={this.handleRadioChange}
                                                    updateSetting={this.updateSetting}
                                                />
                                                <TestEmailPanel
                                                    handleInputChange={this.handleInputChange}
                                                    showNotice={this.showNotice}
                                                />
                                            </div>
                                        )
                                }
                            }
                        }
                    </TabPanel>
                </Fragment>
            );
        }
    }
}

export {KudosAdmin}