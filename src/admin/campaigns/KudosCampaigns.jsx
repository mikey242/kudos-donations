import apiFetch from '@wordpress/api-fetch'
import api from '@wordpress/api'
import { Fragment, useEffect, useRef, useState } from '@wordpress/element'
import { Header } from '../settings/Components/Header'
import { PlusIcon } from '@heroicons/react/outline'
import React from 'react'
import CampaignTable from '../components/CampaignTable'
import CampaignEdit from '../components/CampaignEdit'
import { __ } from '@wordpress/i18n'
import { Spinner } from '@wordpress/components'
import Notification from '../components/Notification'

const KudosCampaigns = () => {
  const [campaigns, setCampaigns] = useState()
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

  const changeCampaign = (campaign) => {
    setCurrentCampaign(campaign)
  }

  const updateCampaign = (id, data = {}) => {
    apiFetch({
      path: `wp/v2/kudos_campaign/${id ?? ''}`,
      method: 'POST',
      data: {
        ...data,
        status: 'publish'
      }
    }).then(() => {
      setCurrentCampaign(null)
      createNotification(data.status === 'draft' ? __('Campaign created', 'kudos-donations') : __('Campaign updated', 'kudos-donations'))
      return getCampaigns()
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
        <Fragment>
            {transactions && campaigns
              ? <Fragment>
                    <Header/>
                    <div className="max-w-3xl w-full mx-auto">
                        {!currentCampaign
                          ? <Fragment>
                                <CampaignTable
                                    transactions={transactions}
                                    deleteClick={removeCampaign}
                                    duplicateClick={duplicateCampaign}
                                    editClick={changeCampaign}
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
              : <div className="absolute inset-0 flex items-center justify-center">
                    <Spinner/>
                </div>
            }
        </Fragment>
  )
}

export { KudosCampaigns }
