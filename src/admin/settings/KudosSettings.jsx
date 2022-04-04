// https://www.codeinwp.com/blog/plugin-options-page-gutenberg/
// https://github.com/HardeepAsrani/my-awesome-plugin/

import { __ } from '@wordpress/i18n'
import { Spinner } from '@wordpress/components'
import { Fragment, useEffect, useState } from '@wordpress/element'
import React from 'react'
import api from '@wordpress/api'
import apiFetch from '@wordpress/api-fetch'

// settings Panels
import { Header } from './Components/Header'
import { IntroGuide } from './Components/IntroGuide'
import { getQueryVar, updateQueryParameter } from '../../common/helpers/util'
import MollieTab from './Components/Tabs/MollieTab'
import { EmailTab } from './Components/Tabs/EmailTab'
import { HelpTab } from './Components/Tabs/HelpTab'
import Button from '../../common/components/controls/Button'
import Notification from '../components/Notification'
import SettingsEdit from '../components/SettingsEdit'
import ReactShadowRoot from 'react-shadow-root'

const KudosSettings = () => {
  const [mollieChanged, setMollieChanged] = useState()
  const [isAPISaving, setIsAPISaving] = useState()
  const [isEdited, setIsEdited] = useState()
  const [isAPILoaded, setIsAPILoaded] = useState(false)
  const [checkingMollie, setCheckingMollie] = useState()
  const [settings, setSettings] = useState()
  const [notification, setNotification] = useState({ shown: false })
  const [isMollieEdited, setIsMollieEdited] = useState()
  const [tabName] = useState(getQueryVar('tab_name', 'mollie'))

  useEffect(() => {
    window.onbeforeunload = (e) => {
      if (isEdited) {
        e.preventDefault()
      }
    }
    getSettings()
  }, [])

  const changeTab = (tab) => {
    updateQueryParameter('tab_name', tab)
  }

  useEffect(() => {
    if (settings) {
      setIsAPILoaded(true)
    }
  }, [settings])

  const checkApiKey = (callback) => {
    setIsAPISaving(true)
    setCheckingMollie(true)

    // Perform Get request
    apiFetch({
      path: 'kudos/v1/payment/test',
      method: 'GET'
    }).then((response) => {
      createNotification(response.data.message)

      // Update state
      setIsAPISaving(false)
      setCheckingMollie(false)
      // setSettings(response.data.settings)

      if (typeof callback === 'function') {
        callback(response)
      }
    })
  }

  const handleInputChange = (option, value, isEdited = true) => {
    setIsEdited(isEdited)
    setSettings(prev => ({
      ...prev,
      [option]: value
    }))
  }

  const showNotice = (message) => {
    setNotification({
      shown: true,
      message: message
    })
  }

  const hideNotification = () => {
    setNotification(prev => ({
      ...prev,
      shown: false
    }))
  }

  // Returns an object with only _kudos prefixed settings
  const filterSettings = (settings) => {
    return Object.fromEntries(
      Object.entries(settings).filter(
        ([key]) => key.startsWith('_kudos')
      )
    )
  }

  // Get the settings from the database
  const getSettings = () => {
    api.loadPromise.then(() => {
      const settings = new api.models.Settings()
      settings.fetch().then((response) => {
        setSettings(filterSettings(response))
      })
    })
  }

  // Update all settings
  const updateSettings = (data, callback) => {
    setIsAPISaving(true)

    // Delete empty settings keys
    for (const key in data) {
      if (data[key] === null) {
        delete data[key]
      }
    }

    // Create WordPress settings model
    const model = new api.models.Settings(data)

    // Save to database
    model
      .save()
      .then((response) => {
        // Commit state
        setSettings(filterSettings(response))
        setIsAPISaving(false)
        setIsEdited(false)
        if (showNotice) {
          createNotification(__('Setting(s) updated', 'kudos-donations'))
        }
        if (isMollieEdited) {
          checkApiKey(showNotice, callback)
          setIsMollieEdited(false)
        } else {
          if (typeof callback === 'function') {
            callback(response)
          }
        }
      })
      .fail((response) => {
        if (showNotice) {
          createNotification(response.statusText)
        }
      })
  }

  const createNotification = (message) => {
    setNotification({
      message: message,
      shown: true
    })
    setTimeout(() => {
      hideNotification()
    }, 2000)
  }

  // Update an individual setting, uses current state if value not specified
  const updateSetting = (option, value, showNotice = false, noticeText = __('Setting updated', 'kudos-donations')) => {
    setIsAPISaving(true)

    // Create WordPress settings model
    const model = new api.models.Settings({
      [option]: value ?? settings[option]
    })

    // Save to database
    model.save().then((response) => {
      // Commit state
      setSettings(filterSettings(response))
      setIsAPISaving(false)
      if (showNotice) {
        createNotification(noticeText)
      }
    })
  }

  // Define tabs and panels
  const tabs = [
    {
      name: 'mollie',
      title: __('Mollie', 'kudos-donations'),
      content:
                <MollieTab
                    settings={settings}
                    mollieChanged={() => setMollieChanged(true)}
                    checkApiKey={checkApiKey}
                />
    },
    {
      name: 'email',
      title: __('Email', 'kudos-donations'),
      content:
                <EmailTab
                    settings={settings}
                    handleInputChange={handleInputChange}
                    showNotice={showNotice}
                />
    },
    {
      name: 'help',
      title: __('Help', 'kudos-donations'),
      content:
                <HelpTab
                    settings={settings}
                    handleInputChange={handleInputChange}
                    updateSettings={updateSettings}
                    updateSetting={updateSetting}
                />
    }

  ]

  return (
        // Show spinner if not yet loaded
        <ReactShadowRoot>
            <link rel="stylesheet"
                  href="/wp-content/plugins/kudos-donations/dist/admin/kudos-admin-settings.css"/>

            {!isAPILoaded
              ? <div className="absolute inset-0 flex items-center justify-center">
                    <Spinner/>
                </div>
              : ''}

            {settings?._kudos_show_intro

              ? <IntroGuide
                    updateSettings={updateSettings}
                    mollieChanged={() => setMollieChanged(true)}
                    isAPISaving={isAPISaving}
                    settings={settings}
                    handleInputChange={handleInputChange}
                    updateSetting={updateSetting}
                />
              : ''}

            {isAPILoaded &&

                <Fragment>

                    <Header>
                        <div className="flex items-center">
                    <span
                        className={`${
                            settings._kudos_vendor_mollie.connected && 'connected'
                        } kudos-api-status text-gray-600 mr-2`}
                    >
                        {checkingMollie
                          ? __('Checking', 'kudos-donations')
                          : settings._kudos_vendor_mollie.connected
                            ? __('Mollie connected', 'kudos-donations')
                            : __('Not connected', 'kudos-donations')
                        }
                    </span>
                            <span
                                className={`${settings._kudos_vendor_mollie.connected ? 'bg-green-600' : 'bg-gray-500'} rounded-full inline-block align-middle mr-2 border-2 border-solid border-gray-300 w-4 h-4`}/>
                            <Button
                                form="settings-form"
                                type="submit"
                            >
                                {__('Save', 'kudos-donations')}
                            </Button>
                        </div>
                    </Header>
                    <SettingsEdit
                        settings={settings}
                        updateSettings={updateSettings}
                        tabs={tabs}
                    />
                    <Notification notification={notification} onClick={hideNotification}/>

                </Fragment>
            }
        </ReactShadowRoot>
  )
}

export { KudosSettings }
