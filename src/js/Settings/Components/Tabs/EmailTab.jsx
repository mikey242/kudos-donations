import {EmailReceiptsPanel} from "../Panels/EmailReceiptsPanel"
import {EmailSettingsPanel} from "../Panels/EmailSettingsPanel"
import {TestEmailPanel} from "../Panels/TestEmailPanel"

const {Card, CardDivider} = wp.components

const EmailTab = (props) => {

    return (
        <Card>
            <EmailReceiptsPanel
                settings={props.settings}
                handleInputChange={props.handleInputChange}
            />
            <CardDivider/>
            <EmailSettingsPanel
                settings={props.settings}
                handleInputChange={props.handleInputChange}
            />
            <CardDivider/>
            <TestEmailPanel
                handleInputChange={props.handleInputChange}
                showNotice={props.showNotice}
            />
        </Card>
    )
}

export {EmailTab}
