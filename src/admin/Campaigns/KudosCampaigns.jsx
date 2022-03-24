import apiFetch from '@wordpress/api-fetch'
import { Fragment, useEffect, useState } from '@wordpress/element'
import { Header } from '../Settings/Components/Header'
import { Spinner } from '@wordpress/components'
import { Icon, plus } from '@wordpress/icons'
import React from 'react'
import Table from '../Components/Table'
import CampaignEdit from '../Components/CampaignEdit'
import { __ } from '@wordpress/i18n'

const KudosCampaigns = () => {
  const [campaigns, setCampaigns] = useState()
  const [currentCampaign, setCurrentCampaign] = useState()
  const [transactions, setTransactions] = useState()
  const [apiReady, setApiReady] = useState(false)

  useEffect(() => {
    getCampaigns()
    getTransactions()
  }, [])

  const addCampaign = (name) => {
    // Add new campaign with defaults.
    const data = {
      title: 'New campaign',
      status: 'publish',
      meta: {
        modal_title: __('Support us!', 'kudos-donations'),
        welcome_text: __('Your support is greatly appreciated and will help to keep us going.', 'kudos-donations'),
        donation_type: 'oneoff',
        amount_type: 'both',
        fixed_amounts: '5,10,20,50'
      }
    }
    return apiFetch({
      path: 'wp/v2/kudos_campaign',
      method: 'POST',
      data: data
    }).then((response) => {
      setCampaigns([...campaigns, response])
      setCurrentCampaign(response)
      getCampaigns()
    })
  }

  const updateCampaign = (id, data = {}) => {
    apiFetch({
      path: `wp/v2/kudos_campaign/${id ?? ''}`,
      method: 'POST',
      data: data
    }).then((response) => {
      return getCampaigns()
    }).then(() => {
      setCurrentCampaign(null)
    })
  }

  const removeCampaign = (id) => {
    apiFetch({
      path: `wp/v2/kudos_campaign/${id}`,
      method: 'DELETE'
    }).then((response) => {
      return getCampaigns()
    }).then(() => {
      setCurrentCampaign(null)
    })
  }

  const getCampaigns = () => {
    return apiFetch({
      path: 'wp/v2/kudos_campaign',
      method: 'GET'
    }).then((response) => {
      setCampaigns(response)
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

  return (
        <Fragment>
            {transactions && campaigns
              ? <Fragment>
                    <Header/>
                    <div className="max-w-3xl flex flex-col items-center mx-auto">
                        {!currentCampaign
                          ? <Fragment>
                                <Table transactions={transactions} deleteClick={removeCampaign}
                                       editClick={setCurrentCampaign}
                                       campaigns={campaigns}/>
                                <button className="rounded-full bg-white mt-5 shadow-md border-0 cursor-pointer"
                                        onClick={addCampaign}><Icon
                                    fill={'currentColor'}
                                    icon={plus}/>
                                </button>
                            </Fragment>
                          : <CampaignEdit updateCampaign={updateCampaign} campaign={currentCampaign}/>
                        }
                    </div>
                </Fragment>
              : <div className="absolute inset-0 flex items-center justify-center">
                    <Spinner/>
                </div>
            }
        </Fragment>
  )
}

export { KudosCampaigns }
