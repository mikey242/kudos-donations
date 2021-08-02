import {NewCampaignPanel} from "../Panels/NewCampaignPanel"
import {CampaignPanel} from "../Panels/CampaignPanel"
import {getQueryVar} from "../../../../common/helpers/util"
import {__} from "@wordpress/i18n"
import {Card, CardHeader, CardDivider, SelectControl} from "@wordpress/components"
import {Fragment, useState} from "@wordpress/element"

const CampaignsTab = (props) => {

    let campaigns = props.settings._kudos_campaigns

    const [campaign, setCampaign] = useState(() => {
        let index = campaigns.findIndex(element => element.id === getQueryVar('campaign_id'))
        return index >= 0 ? index : campaigns[0] ? 0 : ''
    })

    const updateCampaign = (value) => {
        let campaign = value >= 0 ? value : ''
        setCampaign(campaign)
    }

    const addCampaign = (name) => {
        // Add new campaign with defaults. Slug is generated server-side in sanitize callback.
        campaigns.push({
            name: name,
            modal_title: __('Support us!', 'kudos-donations'),
            welcome_text: __('Your support is greatly appreciated and will help to keep us going.', 'kudos-donations'),
            donation_type: 'oneoff',
            amount_type: 'both',
            fixed_amounts: '5,10,20,50'
        })

        // Save changes
        /* translators: %s: Name of campaign added. */
        props.updateSetting('_kudos_campaigns', null, true, sprintf(__('Added campaign "%s"', 'kudos-donations'), name))
        updateCampaign(campaigns.length - 1)
    }

    const removeCampaign = (id) => {
        // Remove where id = provided id
        let updated = campaigns.filter(value => value.id !== id)

        // Save changes
        props.updateSetting('_kudos_campaigns', _.uniq(updated, 'id'), true, __('Campaign deleted', 'kudos-donations'))
        updateCampaign(campaigns.length - 2)
    }

    return (
        <Fragment>
            <Card>
                <NewCampaignPanel
                    settings={props.settings}
                    handleInputChange={props.handleInputChange}
                    addCampaign={addCampaign}
                    isAPISaving={props.isAPISaving}
                />
                <CardDivider/>
            </Card>

            <br/>

            <Card>
                <CardHeader className={"kd-box-border"}>
                    <div>
                        <h3>{__('Campaign details', 'kudos-donations')}</h3>
                        {typeof campaigns[campaign] !== 'undefined' ?
                            <span>campaign_id: <strong>{campaigns[campaign].id}</strong></span>
                            : ''}
                    </div>
                    <div className="kudos-campaign-selector">
                        <SelectControl
                            label={__('Select a campaign:', 'kudos-donations')}
                            labelPosition="side"
                            value={campaign}
                            onChange={(value) =>
                                updateCampaign(value)
                            }
                            options={
                                [{
                                    value: '',
                                    label: __('Select a campaign', 'kudos-donations'),
                                    disabled: true
                                }].concat(
                                    campaigns.map((campaign, i) => {
                                        return {
                                            value: i, label: campaign.name
                                        }
                                    })
                                )
                            }
                        />
                    </div>

                </CardHeader>

                {typeof campaigns[campaign] !== 'undefined' ?

                    <CampaignPanel
                        allowDelete={campaign !== 0}
                        settings={props.settings}
                        campaign={campaigns[campaign]}
                        removeCampaign={removeCampaign}
                        handleInputChange={props.handleInputChange}
                    />

                    : null}

            </Card>

        </Fragment>
    )
}

export {CampaignsTab}
