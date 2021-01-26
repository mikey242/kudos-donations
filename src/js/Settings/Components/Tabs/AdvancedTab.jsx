import {ExportSettingsPanel} from "../Panels/ExportSettingsPanel"
import {ImportSettingsPanel} from "../Panels/ImportSettingsPanel"
import {DebugModePanel} from "../Panels/DebugModePanel"

const {Card, CardDivider} = wp.components

const AdvancedTab = (props) => {

    return (
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
                handleInputChange={props.handleInputChange}
            />
        </Card>
    )
}

export {AdvancedTab}
