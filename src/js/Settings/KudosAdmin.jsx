// https://www.codeinwp.com/blog/plugin-options-page-gutenberg/
// https://github.com/HardeepAsrani/my-awesome-plugin/

import axios from 'axios'

// Settings Panels
import {Notice} from './Components/Notice'
import {Header} from './Components/Header'
import {IntroGuide} from "./Components/IntroGuide"
import {getQueryVar, updateQueryParameter} from "./Helpers/Util"
import {MollieTab} from "./Components/Tabs/MollieTab"
import {CampaignsTab} from "./Components/Tabs/CampaignsTab"
import {CustomizeTab} from "./Components/Tabs/CustomizeTab"
import {EmailTab} from "./Components/Tabs/EmailTab"
import {HelpTab} from "./Components/Tabs/HelpTab"

const {__} = wp.i18n

const {
    Spinner,
    TabPanel,
} = wp.components
const {Component, Fragment} = wp.element
const {applyFilters} = wp.hooks

class KudosAdmin extends Component {
    constructor() {
        super(...arguments)

        this.changeTab = this.changeTab.bind(this)
        this.updateSetting = this.updateSetting.bind(this)
        this.updateAll = this.updateAll.bind(this)
        this.handleInputChange = this.handleInputChange.bind(this)
        this.mollieChanged = this.mollieChanged.bind(this)
        this.showNotice = this.showNotice.bind(this)
        this.hideNotice = this.hideNotice.bind(this)
        this.checkApiKey = this.checkApiKey.bind(this)

        this.state = {
            tabName: getQueryVar('tab_name', 'mollie'),
            showNotice: false,
            noticeMessage: '',
            isMollieEdited: false,
            isEdited: false,
            isAPILoaded: false,
            isAPISaving: false,
            checkingApi: false,
            campaigns: [],
            settings: {}
        }

        this.tabs = {}
    }

    componentDidMount() {
        window.onbeforeunload = (e) => {
            if (this.state.isEdited) {
                e.preventDefault()
            }
        }
        if (false === this.state.isAPILoaded) {
            this.getSettings()
        }
    }

    mollieChanged() {
        this.setState({
            isMollieEdited: true,
            settings: {
                _kudos_mollie_connected: false,
            },
        })
    }

    changeTab(tab) {
        updateQueryParameter('tab_name', tab)
    }

    checkApiKey() {
        this.setState({
            checkingApi: true,
            isAPISaving: true,
        })

        // Create form data from current state
        // eslint-disable-next-line no-undef
        const formData = new FormData()
        formData.append('action', 'check_mollie_connection')
        formData.append(
            'apiMode',
            this.state.settings._kudos_mollie_api_mode
        )
        formData.append(
            'testKey',
            this.state.settings._kudos_mollie_test_api_key
        )
        formData.append(
            'liveKey',
            this.state.settings._kudos_mollie_live_api_key
        )

        // Perform Get request
        axios
            .get(window.kudos.checkApiUrl, {
                headers: {
                    // eslint-disable-next-line no-undef
                    'X-WP-Nonce': wpApiSettings.nonce,
                },
                params: {
                    apiMode: this.state.settings._kudos_mollie_api_mode,
                    testKey: this.state.settings._kudos_mollie_test_api_key,
                    liveKey: this.state.settings._kudos_mollie_live_api_key,
                },
            })
            .then((response) => {
                this.showNotice(response.data.data)
                this.setState({
                    settings: {
                        ...this.state.settings,
                        _kudos_mollie_connected: response.data.success,
                    },
                    checkingApi: false,
                    isAPISaving: false,
                })
            })
    }

    handleInputChange(option, value) {
        this.setState({
            isEdited: true,
            settings: {
                ...this.state.settings,
                [option]: value,
            },
        })
    }

    showNotice(message) {
        this.setState({
            showNotice: true,
            noticeMessage: message,
        })
    }

    hideNotice() {
        this.setState({
            showNotice: false,
        })
    }

    getSettings() {
        wp.api.loadPromise.then(() => {
            this.settings = new wp.api.models.Settings()
            this.settings.fetch().then((response) => {
                const settings = Object.keys(response)
                    .filter(key => key.startsWith('_kudos'))
                    .reduce((obj, key) => {
                        obj[key] = response[key]
                        return obj
                    }, {})
                this.setState({
                    settings: {...settings},
                    isAPILoaded: true,
                    showNotice: false,
                })
            })
        })
    }

    // Update all settings
    updateAll(showNotice = true) {
        this.setState({isAPISaving: true})

        // Delete empty settings keys
        for (const key in this.state.settings) {
            if (this.state.settings[key] === null) {
                delete this.state.settings[key]
            }
        }

        //Create WordPress settings model
        const model = new wp.api.models.Settings({
            ...this.state.settings,
        })

        //Save to database
        model
            .save()
            .then((response) => {
                // Commit state
                this.setState({
                    settings: {
                        ...response,
                    },
                    isEdited: false,
                    isAPISaving: false,
                })
                if (showNotice) {
                    this.showNotice(
                        __('Setting(s) updated', 'kudos-donations')
                    )
                }
                if (this.state.isMollieEdited) {
                    this.checkApiKey()
                    this.setState({
                        isMollieEdited: false,
                    })
                }
            })
            .fail((response) => {
                if (showNotice) {
                    this.showNotice(response.statusText)
                }
            })
    }

    // Update an individual setting, uses current state if value not specified
    updateSetting(option, value, showNotice=false, noticeText=__('Setting updated', 'kudos-donations')) {
        this.setState({isAPISaving: true})

        //Create WordPress settings model
        const model = new wp.api.models.Settings({
            [option]: value ?? this.state.settings[option],
        })

        //Save to database
        model.save().then((response) => {
            // Commit state
            this.setState({
                settings: {
                    ...response
                },
                isAPISaving: false,
            })
            if (showNotice) {
                this.showNotice(
                    noticeText
                )
            }
        })
    }

    render() {

        // Show spinner if not yet loaded
        if (!this.state.isAPILoaded) {
            return (
                <div className="kd-absolute kd-inset-0 kd-flex kd-items-center kd-justify-center">
                    <Spinner/>
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
                    <MollieTab
                        settings={this.state.settings}
                        mollieChanged={this.mollieChanged}
                        handleInputChange={this.handleInputChange}
                    />
            },
            {
                name: 'campaigns',
                title: __('Campaigns', 'kudos-donations'),
                className: 'tab-campaigns',
                content:
                    <CampaignsTab
                        settings={this.state.settings}
                        handleInputChange={this.handleInputChange}
                        updateSetting={this.updateSetting}
                    />
            },
            {
                name: 'customize',
                title: __('Customize', 'kudos-donations'),
                className: 'tab-customize',
                content:
                    <CustomizeTab
                        settings={this.state.settings}
                        handleInputChange={this.handleInputChange}
                    />
            },
            {
                name: 'email',
                title: __('Email', 'kudos-donations'),
                className: 'tab-email',
                content:
                    <EmailTab
                        settings={this.state.settings}
                        handleInputChange={this.handleInputChange}
                        showNotice={this.showNotice}
                    />
            },
            {
                name: 'help',
                title: __('Help', 'kudos-donations'),
                className: 'tab-help',
                content:
                    <HelpTab
                        settings={this.state.settings}
                        handleInputChange={this.handleInputChange}
                        updateAll={this.updateAll}
                        updateSetting={this.updateSetting}
                    />
            }

        ], this)

        return (

            <Fragment>

                <Notice
                    showNotice={this.state.showNotice}
                    hideNotice={this.hideNotice}
                    message={this.state.noticeMessage}
                />

                <Header
                    {...this.state}
                    updateAll={this.updateAll}
                />

                <TabPanel
                    className="kudos-tab-panel kd-mx-auto kd-mt-5 kd-container"
                    onSelect={(tab) => {
                        this.changeTab(tab)
                    }}
                    activeClass="is-active"
                    initialTabName={this.state.tabName}
                    tabs={
                        Object.entries(this.tabs).map((tab) => {
                            tab = tab[1]
                            return tab
                        })
                    }
                >
                    {(tab) => {
                        return (
                            <div className="kudos-settings-main">

                                {tab.content}

                            </div>
                        )
                    }}
                </TabPanel>


                <IntroGuide
                    show={this.state.settings._kudos_show_intro}
                    updateSetting={this.updateSetting}
                />

            </Fragment>
        )
    }
}

export {KudosAdmin}
