import apiFetch from '@wordpress/api-fetch'
import api from '@wordpress/api'
import { Fragment, useEffect, useRef, useState } from '@wordpress/element'
import { Header } from '../settings/Components/Header'
import { PlusIcon } from '@heroicons/react/outline'
import React from 'react'
import CampaignTable from '../components/CampaignTable'
import CampaignEdit from '../components/CampaignEdit'
import { __ } from '@wordpress/i18n'
import loader from '../../images/loader.svg'
import Notification from '../components/Notification'
import Button from '../../common/components/controls/Button'
import { getQueryVar, removeQueryParameter, updateQueryParameter } from '../../common/helpers/util'
import ReactShadowRoot from 'react-shadow-root'

const KudosCampaigns = () => {
  const [campaigns, setCampaigns] = useState()
  const [isApiBusy, setIsApiBusy] = useState(true)
  const [campaignSlug, setCampaignSlug] = useState(getQueryVar('campaign'))
  const [notification, setNotification] = useState({ shown: false })
  const [currentCampaign, setCurrentCampaign] = useState(null)
  const [transactions, setTransactions] = useState()
  const [vendorSettings, setVendorSettings] = useState()
  const notificationTimer = useRef(null)

  useEffect(() => {
    getCampaigns()
    getTransactions()
    getSettings()
  }, [])

  useEffect(() => {
    currentCampaign ? updateQueryParameter('campaign', currentCampaign.slug) : removeQueryParameter('campaign')
  }, [currentCampaign])

  const newCampaign = () => {
    setCurrentCampaign({
      status: 'draft',
      meta: {
        initial_title: __('Support us!', 'kudos-donations'),
        initial_text: __('Your support is greatly appreciated and will help to keep us going.', 'kudos-donations'),
        donation_type: 'oneoff',
        amount_type: 'both',
        fixed_amounts: '5,10,20,50',
        theme_color: '#ff9f1c'
      }
    })
  }

  const createNotification = (message) => {
    setNotification({
      message: message,
      shown: true
    })
    clearTimeout(notificationTimer.current)
    notificationTimer.current = setTimeout(() => {
      hideNotification()
    }, 2000)
    return () => {
      clearTimeout(notificationTimer.current)
    }
  }

  const updateCampaign = (id, data = {}) => {
    setIsApiBusy(true)
    apiFetch({
      path: `wp/v2/kudos_campaign/${id ?? ''}`,
      method: 'POST',
      data: {
        ...data,
        status: 'publish'
      }
    }).then(() => {
      return getCampaigns()
    }).then(() => {
      setIsApiBusy(false)
      createNotification(data.status === 'draft' ? __('Campaign created', 'kudos-donations') : __('Campaign updated', 'kudos-donations'))
      setCurrentCampaign(null)
    })
  }

  const removeCampaign = (id) => {
    apiFetch({
      path: `wp/v2/kudos_campaign/${id}`,
      method: 'DELETE'
    }).then(() => {
      createNotification(__('Campaign deleted', 'kudos-donations'))
      return getCampaigns()
    })
  }

  const duplicateCampaign = (campaign) => {
    const data = {
      ...campaign,
      id: null,
      title: campaign.title.rendered,
      status: 'draft'
    }
    updateCampaign(null, data)
  }

  const getCampaigns = () => {
    return apiFetch({
      path: 'wp/v2/kudos_campaign',
      method: 'GET'
    }).then((response) => {
      setCampaigns(response.reverse())
      // if (campaignSlug) {
      //   setCurrentCampaign(response.filter(campaign => campaign.slug === campaignSlug)[0])
      // }
    })
  }

  const getTransactions = () => {
    apiFetch({
      path: 'kudos/v1/transaction/all/',
      method: 'GET'
    }).then((response) => {
      setTransactions(response)
    })
  }

  const getSettings = () => {
    api.loadPromise.then(() => {
      const settings = new api.models.Settings()
      settings.fetch().then((response) => {
        setVendorSettings(response._kudos_vendor_mollie)
      })
    })
  }

  const hideNotification = () => {
    setNotification(prev => ({
      ...prev,
      shown: false
    }))
  }

  return (
        <ReactShadowRoot>
            {transactions && campaigns &&
                <Fragment>
                    <link rel="stylesheet"
                          href="/wp-content/plugins/kudos-donations/dist/admin/kudos-admin-settings.css"/>
                    <Header>
                        {currentCampaign &&
                            <Button form="settings-form" type="submit">
                                {currentCampaign.status === 'draft' ? __('Create', 'kudos-donations') : __('Save', 'kudos-donations')}
                            </Button>
                        }
                    </Header>
                    <div className="max-w-3xl w-full mx-auto">
                        {!currentCampaign
                          ? <Fragment>
                                <CampaignTable
                                    transactions={transactions}
                                    deleteClick={removeCampaign}
                                    duplicateClick={duplicateCampaign}
                                    editClick={setCurrentCampaign}
                                    campaigns={campaigns}
                                />
                                <button
                                    title={__('Add campaign', 'kudos-donations')}
                                    className="rounded-full mx-auto p-2 flex justify-center items-center bg-white mt-5 shadow-md border-0 cursor-pointer"
                                    onClick={newCampaign}>
                                    <PlusIcon
                                        className={'w-5 h-5'}
                                    />
                                </button>
                            </Fragment>
                          : <CampaignEdit
                                updateCampaign={updateCampaign}
                                recurringAllowed={vendorSettings?.recurring}
                                setCurrentCampaign={setCurrentCampaign}
                                campaign={currentCampaign}
                            />

                        }
                    </div>

                    <Notification notification={notification} onClick={hideNotification}/>

                </Fragment>
            }
        </ReactShadowRoot>
  )
}

export { KudosCampaigns }
