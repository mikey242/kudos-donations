// https://www.codeinwp.com/blog/plugin-options-page-gutenberg/
// https://github.com/HardeepAsrani/my-awesome-plugin/

import {__} from "@wordpress/i18n"
import {Spinner, TabPanel} from "@wordpress/components"
import {Component, Fragment} from "@wordpress/element"
import {applyFilters} from "@wordpress/hooks"
import api from '@wordpress/api'
import apiFetch from '@wordpress/api-fetch'

// Settings Panels
import {Notice} from './Components/Notice'
import {Header} from './Components/Header'
import {IntroGuide} from "./Components/IntroGuide"
import {getQueryVar, updateQueryParameter} from "../../common/helpers/util"
import {MollieTab} from "./Components/Tabs/MollieTab"
import {CampaignsTab} from "./Components/Tabs/CampaignsTab"
import {CustomizeTab} from "./Components/Tabs/CustomizeTab"
import {EmailTab} from "./Components/Tabs/EmailTab"
import {HelpTab} from "./Components/Tabs/HelpTab"

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
        })
    }

    changeTab(tab) {
        updateQueryParameter('tab_name', tab)
    }

    checkApiKey(showNotice = true, callback) {

        this.setState({
            checkingApi: true,
            isAPISaving: true,
        })

        // Perform Get request
        apiFetch({
            path: 'kudos/v1/payment/test',
            method: 'GET',
        }).then((response) => {

                if (showNotice) {
                    this.showNotice(response.data.message)
                }

                // Update state
                this.setState({
                    checkingApi: false,
                    isAPISaving: false,
                    settings: {
                        ...this.state.settings,
                        _kudos_vendor_mollie: {
                            ...response.data.setting
                        }
                    }
                })

                if (typeof callback === "function") {
                    callback(response)
                }
            })
    }

    handleInputChange(option, value, isEdited = true) {
        this.setState({
            isEdited: isEdited,
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

    // Returns an object with only _kudos prefixed settings
    filterSettings(settings) {
        return Object.fromEntries(
            Object.entries(settings).filter(
                ([key]) => key.startsWith('_kudos')
            )
        )
    }

    // Get the settings from the database
    getSettings() {
        api.loadPromise.then(() => {
            let settings = new api.models.Settings()
            settings.fetch().then((response) => {
                this.setState({
                    settings: this.filterSettings(response),
                    isAPILoaded: true,
                    showNotice: false,
                })
            })
        })
    }

    // Update all settings
    updateAll(showNotice = true, callback) {
        this.setState({isAPISaving: true})

        // Delete empty settings keys
        for (const key in this.state.settings) {
            if (this.state.settings[key] === null) {
                delete this.state.settings[key]
            }
        }

        //Create WordPress settings model
        const model = new api.models.Settings({
            ...this.state.settings,
        })

        //Save to database
        model
            .save()
            .then((response) => {
                // Commit state
                this.setState({
                    settings: {
                        ...this.filterSettings(response),
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
                    this.checkApiKey(showNotice, callback)
                    this.setState({
                        isMollieEdited: false,
                    })
                } else {
                    if (typeof callback === "function") {
                        callback(response)
                    }
                }
            })
            .fail((response) => {
                if (showNotice) {
                    this.showNotice(response.statusText)
                }
            })
    }

    // Update an individual setting, uses current state if value not specified
    updateSetting(option, value, showNotice = false, noticeText = __('Setting updated', 'kudos-donations')) {
        this.setState({isAPISaving: true})

        //Create WordPress settings model
        const model = new api.models.Settings({
            [option]: value ?? this.state.settings[option],
        })

        //Save to database
        model.save().then((response) => {
            // Commit state
            this.setState({
                settings: this.filterSettings(response),
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

        if (this.state.settings._kudos_show_intro) {
            return (
                <IntroGuide
                    updateAll={this.updateAll}
                    mollieChanged={this.mollieChanged}
                    isAPISaving={this.state.isAPISaving}
                    settings={this.state.settings}
                    handleInputChange={this.handleInputChange}
                    updateSetting={this.updateSetting}
                />
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
                        showNotice={this.showNotice}
                        checkApiKey={this.checkApiKey}
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
                        isAPISaving={this.state.isAPISaving}
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
                    className={"kudos-tab-panel kd-mx-auto kd-mt-5 kd-w-[768px]" + (this.state.isAPISaving ? " api-saving" : "")}
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

            </Fragment>
        )
    }
}

export {KudosAdmin}
