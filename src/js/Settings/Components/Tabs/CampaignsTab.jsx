import {AddCampaignPanel} from "../Panels/AddCampaignPanel"
import {CampaignPanel} from "../Panels/CampaignPanel"
import {getQueryVar} from "../../Helpers/Util"

const {Fragment} = wp.element
const {Card, Panel} = wp.components

const CampaignsTab = (props) => {

    const campaignId = getQueryVar('campaignId')

    return (
        <Fragment>
            <Card>
                <AddCampaignPanel
                    settings={props.settings}
                    handleInputChange={props.handleInputChange}
                    updateSetting={props.updateSetting}
                />
            </Card>
            <br/>
            <Panel>
                {props.settings._kudos_campaigns.map((campaign, i) => {

                    return (
                        <CampaignPanel
                            key={'campaign_' + i}
                            isOpen={campaign.id === campaignId}
                            allowDelete={!campaign.protected}
                            settings={props.settings}
                            campaign={props.settings._kudos_campaigns[i]}
                            updateSetting={props.updateSetting}
                            handleInputChange={props.handleInputChange}
                        />
                    )

                })}
            </Panel>
        </Fragment>
    )
}

export {CampaignsTab}
