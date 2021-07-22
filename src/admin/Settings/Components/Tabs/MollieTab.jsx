import {MollieApiModePanel} from "../Panels/MollieApiModePanel"
import {MollieApiKeysPanel} from "../Panels/MollieApiKeysPanel"
import {Card, CardDivider} from "@wordpress/components"

const MollieTab = (props) => {

    return (
        <Card>
            <MollieApiModePanel
                settings={props.settings}
                mollieChanged={props.mollieChanged}
                checkApiKey={props.checkApiKey}
                handleInputChange={props.handleInputChange}
            />
            <CardDivider/>
            <MollieApiKeysPanel
                settings={props.settings}
                mollieChanged={props.mollieChanged}
                handleInputChange={props.handleInputChange}
            />
        </Card>
    )
}

export {MollieTab}
