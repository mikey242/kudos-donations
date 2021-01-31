import {NewCampaignPanel} from "../Panels/NewCampaignPanel"
import {CampaignPanel} from "../Panels/CampaignPanel"
import {getQueryVar} from "../../Helpers/Util"

const {__} = wp.i18n
const {useState, Fragment} = wp.element
const {Card, CardHeader, CardDivider, SelectControl} = wp.components

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
            donation_type: 'both',
            amount_type: 'both',
            fixed_amounts: '5,10,20,50'
        })

        // Save changes
        props.updateSetting('_kudos_campaigns', null, true, sprintf(__('Added campaign "%s"', 'kudos-donations'), name))
        updateCampaign(campaigns.length - 1)
    }

    const removeCampaign = (id) => {
        // Remove where id = provided is
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
                />

                <CardDivider/>
            </Card>

            <br/>

                <Card>

                    <CardHeader>
                        <h3>{__('Campaign details', 'kudos-donations')}</h3>
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

                    { typeof campaigns[campaign] !== 'undefined' ?

                    <CampaignPanel
                        allowDelete={!campaigns[campaign].protected}
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
