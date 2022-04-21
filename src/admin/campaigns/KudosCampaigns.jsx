import apiFetch from '@wordpress/api-fetch'
import api from '@wordpress/api'
import { Fragment, useEffect, useRef, useState } from '@wordpress/element'
import { Header } from '../components/Header'
import { PlusIcon } from '@heroicons/react/outline'
import React from 'react'
import CampaignTable from '../components/CampaignTable'
import CampaignEdit from '../components/CampaignEdit'
import { __ } from '@wordpress/i18n'
import Notification from '../components/Notification'
import { Button } from '../../common/components/controls'
import { getQueryVar, removeQueryParameter, updateQueryParameter } from '../../common/helpers/util'
import EmptyCampaigns from '../components/EmptyCampaigns'
import KudosRender from '../../public/components/KudosRender'

const KudosCampaigns = ({ stylesheet }) => {
  const [campaigns, setCampaigns] = useState()
  const [isApiBusy, setIsApiBusy] = useState(true)
  const [campaignSlug, setCampaignSlug] = useState(getQueryVar('campaign'))
  const [notification, setNotification] = useState({ shown: false })
  const [currentCampaign, setCurrentCampaign] = useState(null)
  const [transactions, setTransactions] = useState()
  const [settings, setSettings] = useState()
  const notificationTimer = useRef(null)

  useEffect(() => {
    getCampaigns()
    getTransactions()
    getSettings()
  }, [])

  useEffect(() => {
    currentCampaign ? updateQueryParameter('campaign', currentCampaign.slug) : removeQueryParameter('campaign')
  }, [currentCampaign])

  useEffect(() => {
    clearTimeout(notificationTimer.current)
    notificationTimer.current = setTimeout(() => {
      hideNotification()
    }, 2000)
    return () => {
      clearTimeout(notificationTimer.current)
    }
  }, [notification])

  const newCampaign = () => {
    setCurrentCampaign({
      status: 'draft',
      title: {
        rendered: __('New campaign', 'kudos-donations')
      },
      meta: {
        initial_title: __('Support us!', 'kudos-donations'),
        initial_text: __('Your support is greatly appreciated and will help to keep us going.', 'kudos-donations'),
        donation_type: 'oneoff',
        amount_type: 'both',
        fixed_amounts: '5,10,20,50',
        theme_color: '#ff9f1c',
        completed_payment: 'message',
        return_message_title: __('Thank you!', 'kudos-donations'),
        return_message_text: __('Many thanks for your donation. We appreciate your support.', 'kudos-donations')
      }
    })
  }

  const createNotification = (message, success) => {
    setNotification({
      message: message,
      success: success,
      shown: true
    })
  }

  const hideNotification = () => {
    setNotification(prev => ({
      ...prev,
      shown: false
    }))
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
      createNotification(
        data.status === 'draft'
          ? __('Campaign created', 'kudos-donations')
          : __('Campaign updated', 'kudos-donations')
      )
      setCurrentCampaign(null)
    }).catch((error) => {
      createNotification(error.message, 'fail')
    }).finally(() => {
      setIsApiBusy(false)
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
        setSettings(response)
      })
    })
  }

  return (
        <>
            {transactions && campaigns &&
                <KudosRender stylesheet={stylesheet.href}>
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
                                {campaigns.length >= 1
                                  ? <CampaignTable
                                        transactions={transactions}
                                        deleteClick={removeCampaign}
                                        duplicateClick={duplicateCampaign}
                                        editClick={setCurrentCampaign}
                                        campaigns={campaigns}
                                    />
                                  : <EmptyCampaigns/>
                                }
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
                                recurringAllowed={settings?.['_kudos_vendor_' + settings._kudos_vendor].recurring}
                                setCurrentCampaign={setCurrentCampaign}
                                campaign={currentCampaign}
                            />

                        }
                    </div>

                    <Notification
                        shown={notification.shown}
                        message={notification.message}
                        success={notification.success}
                        onClick={hideNotification}
                    />

                </KudosRender>
            }
        </>
  )
}

export { KudosCampaigns }
