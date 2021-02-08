import {HelpPanel} from "../Panels/HelpPanel"
import {ExportSettingsPanel} from "../Panels/ExportSettingsPanel"
import {ImportSettingsPanel} from "../Panels/ImportSettingsPanel"
import {DebugModePanel} from "../Panels/DebugModePanel"


const {Card, CardDivider} = wp.components
const {Fragment} = wp.element

const HelpTab = (props) => {

    return (
        <Fragment>
            <Card>
                <HelpPanel
                    updateSetting={props.updateSetting}
                />
            </Card>
            <br/>
            <Card>
                <ExportSettingsPanel
                    settings={props.settings}
                />
                <CardDivider/>
                <ImportSettingsPanel
                    updateAll={props.updateAll}
                    handleInputChange={props.handleInputChange}
                />
                <CardDivider/>
                <DebugModePanel
                    settings={props.settings}
                    updateSetting={props.updateSetting}
                />
            </Card>
        </Fragment>
    )
}

export {HelpTab}
