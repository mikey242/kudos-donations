/**
 * WordPress dependencies
 */


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

/**
 * Internal dependencies
 */

import axios from "axios"
import {KudosNotice} from "./KudosNotice";
import {KudosHeader} from "./KudosHeader";
import {EmailSettingsPanel} from "./Panels/EmailSettingsPanel";
import {TestEmailPanel} from "./Panels/TestEmailPanel";
import {MolliePanel} from "./Panels/MolliePanel";

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

            console.log('getting settings')
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
            this.setState({
                _kudos_mollie_connected: (response.data.success)
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

    updateSetting( option, value) {

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
            this.showNotice(__('Setting(s) updated', 'kudos-donations'));
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
                                name: 'email',
                                title: 'Email',
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
                                                    apiMode={this.state._kudos_mollie_api_mode}
                                                    isSaving = {this.state.isAPISaving}
                                                    testKey={this.state._kudos_mollie_test_api_key}
                                                    liveKey={this.state._kudos_mollie_live_api_key}
                                                    handleInputChange={this.handleInputChange}
                                                    updateSetting={this.updateSetting}
                                                    checkApiKey={this.checkApiKey}
                                                />
                                            </div>
                                        )

                                    case 'email':

                                        return (
                                            <div className="kudos-settings-main dashboard-wrap" key='kudos-settings'>
                                                <EmailSettingsPanel
                                                    isSaving = {this.state.isAPISaving}
                                                    enableSmtp={this.state._kudos_smtp_enable}
                                                    host={this.state._kudos_smtp_host}
                                                    encryption={this.state._kudos_smtp_encryption}
                                                    autoTls={this.state._kudos_smtp_autotls}
                                                    username={this.state._kudos_smtp_username}
                                                    password={this.state._kudos_smtp_password}
                                                    port={this.state._kudos_smtp_port}
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