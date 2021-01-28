import {AddCampaignPanel} from "../Panels/AddCampaignPanel"
import {CampaignPanel} from "../Panels/CampaignPanel"
import {SettingCard} from "../SettingCard"
import {getQueryVar, updateQueryParameter} from "../../Helpers/Util"

const {__} = wp.i18n
const {useState, Fragment} = wp.element
const {Card, CardDivider, SelectControl} = wp.components

const CampaignsTab = (props) => {

    let campaigns = props.settings._kudos_campaigns

    const [campaign, setCampaign] = useState(() => {
            let index = campaigns.findIndex(element => element.id === getQueryVar('campaign_id'))
            return index >= 0 ? index : ''
    })

    const changeCampaign = (value) => {
        setCampaign(value)
        updateQueryParameter('campaign_id', campaigns[value].id)
    }

    const addCampaign = (name) => {
        // Add new campaign with defaults
        campaigns.push({
            name: name,
            modal_title: __('Support us!', 'kudos-donations'),
            welcome_text: __('Your support is greatly appreciated and will help to keep us going.', 'kudos-donations'),
            donation_type: 'both',
            amount_type: 'both',
            fixed_amounts: '5,10,20,50'
        })

        // Save changes
        props.updateSetting('_kudos_campaigns', null, true, sprintf(__('Added campaign "%s".', 'kudos-donations'), name))
        setCampaign(campaigns.length - 1)
    }

    const removeCampaign = (id) => {
        let updated = campaigns.filter(value => value.id !== id)
        changeCampaign(campaigns.length - 2)
        props.updateSetting('_kudos_campaigns', _.uniq(updated, 'id'), true, __('Campaign deleted.', 'kudos-donations'))
    }

    return (
        <Fragment>
            <Card>
                <AddCampaignPanel
                    settings={props.settings}
                    handleInputChange={props.handleInputChange}
                    addCampaign={addCampaign}
                />

                <CardDivider/>

                <SettingCard title={__('Edit campaign:', 'kudos-donations')}>
                    <SelectControl
                        className="kd-block"
                        value={campaign}
                        onChange={(value) =>
                            changeCampaign(value)
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
                </SettingCard>
            </Card>

            <br/>

            { campaign !== null && campaign !== '' ?

                <Card>

                    <CampaignPanel
                        allowDelete={!campaigns[campaign].protected}
                        settings={props.settings}
                        campaign={campaigns[campaign]}
                        removeCampaign={removeCampaign}
                        handleInputChange={props.handleInputChange}
                    />

                </Card>

            : null}

        </Fragment>
    )
}

export {CampaignsTab}
