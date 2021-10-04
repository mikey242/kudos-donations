import {HelpPanel} from "../Panels/HelpPanel"
import {ExportSettingsPanel} from "../Panels/ExportSettingsPanel"
import {ImportSettingsPanel} from "../Panels/ImportSettingsPanel"
import {DebugModePanel} from "../Panels/DebugModePanel"
import {RenderModalFooter} from "../Panels/RenderModalFooter"
import {Card, CardDivider} from "@wordpress/components"
import {Fragment} from "@wordpress/element"
import {DisableShortcodePanel} from "../Panels/DisableShortcodePanel"

const HelpTab = (props) => {

    return (
        <Fragment>
            <Card>
                <HelpPanel
                    handleInputChange={props.handleInputChange}
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
                <RenderModalFooter
                    settings={props.settings}
                    handleInputChange={props.handleInputChange}
                />
                <CardDivider/>
                <DebugModePanel
                    settings={props.settings}
                    handleInputChange={props.handleInputChange}
                />
                <CardDivider/>
                <DisableShortcodePanel
                    settings={props.settings}
                    handleInputChange={props.handleInputChange}
                />
            </Card>
        </Fragment>
    )
}

export {HelpTab}
