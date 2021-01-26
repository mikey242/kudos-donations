import {AddCampaignPanel} from "../Panels/AddCampaignPanel"
import {CampaignPanel} from "../Panels/CampaignPanel"

const {Fragment} = wp.element
const {Card, Panel} = wp.components

const CampaignsTab = (props) => {

    return (
        <Fragment>
            <Card>
                <AddCampaignPanel
                    settings={props.settings}
                    showNotice={props.showNotice}
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
                            allowDelete={!campaign.protected}
                            settings={props.settings}
                            campaign={props.settings._kudos_campaigns[i]}
                            updateSetting={props.updateSetting}
                            showNotice={props.showNotice}
                            handleInputChange={props.handleInputChange}
                        />
                    )

                })}
            </Panel>
        </Fragment>
    )
}

export {CampaignsTab}
