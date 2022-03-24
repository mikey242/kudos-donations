import { NewCampaignPanel } from '../Panels/NewCampaignPanel'
import { CampaignPanel } from '../Panels/CampaignPanel'
import { getQueryVar } from '../../../../common/helpers/util'
import React from 'react'
import { __ } from '@wordpress/i18n'
import { Card, CardHeader, CardDivider, SelectControl } from '@wordpress/components'
import { Fragment, useState, useEffect } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

const CampaignsTab = (props) => {
  const { isAPISaving, isRecurringAllowed, settings } = props
  const { handleInputChange } = props
  const [campaigns, setCampaigns] = useState()

  const [campaign, setCampaign] = useState(() => {
    return ''
    // const index = campaigns.findIndex(element => element.id === getQueryVar('campaign_id'))
    // return index >= 0 ? index : campaigns[0] ? 0 : ''
  })

  useEffect(() => {
    getCampaigns()
  }, [])

  const addCampaign = (name) => {
    // Add new campaign with defaults.
    const data = {
      title: name,
      status: 'publish',
      meta: {
        modal_title: __('Support us!', 'kudos-donations'),
        welcome_text: __('Your support is greatly appreciated and will help to keep us going.', 'kudos-donations'),
        donation_type: 'oneoff',
        amount_type: 'both',
        fixed_amounts: '5,10,20,50'
      }
    }
    apiFetch({
      path: 'wp/v2/kudos_campaign',
      method: 'POST',
      data: data
    }).then((response) => {
      setCampaign(response)
      getCampaigns()
    })
  }

  const getCampaigns = () => {
    return apiFetch({
      path: 'wp/v2/kudos_campaign',
      method: 'GET'
    }).then((response) => {
      setCampaigns(response)
      if (!campaign) setCampaign(response[0])
    })
  }

  return (
        <>

            <Fragment>
                <Card>
                    <NewCampaignPanel
                        handleInputChange={handleInputChange}
                        addCampaign={addCampaign}
                        isAPISaving={isAPISaving}
                    />
                    <CardDivider/>
                </Card>

                <br/>
                {campaign &&
                    <Card>
                        <CardHeader className={'box-border'}>
                            <div>
                                <h3>{__('Campaign details', 'kudos-donations')}</h3>
                                <span>campaign_id: <strong>{campaign.slug}</strong></span>

                            </div>
                            <div className="kudos-campaign-selector">
                                <SelectControl
                                    label={__('Select a campaign:', 'kudos-donations')}
                                    labelPosition="side"
                                    value={campaign.slug}
                                    onChange={(value) => setCampaign(campaigns.find(o => o.slug === value))}
                                    options={
                                        [{
                                          value: '',
                                          label: __('Select a campaign', 'kudos-donations'),
                                          disabled: true
                                        }].concat(
                                          campaigns.map((campaign, i) => {
                                            return {
                                              value: campaign.slug, label: campaign.title?.rendered
                                            }
                                          })
                                        )
                                    }
                                />
                            </div>

                        </CardHeader>

                        {typeof campaign !== 'undefined'

                          ? <CampaignPanel
                                allowDelete={campaign !== campaigns[0]}
                                settings={settings}
                                isRecurringAllowed={isRecurringAllowed}
                                campaign={campaign}
                                removeCampaign={removeCampaign}
                                handleInputChange={handleInputChange}
                            />

                          : null}

                    </Card>
                }
            </Fragment>

        </>
  )
}

export { CampaignsTab }
