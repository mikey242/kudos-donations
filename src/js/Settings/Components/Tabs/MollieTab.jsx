import {MollieApiModePanel} from "../Panels/MollieApiModePanel"
import {MollieApiKeysPanel} from "../Panels/MollieApiKeysPanel"

const {Card, CardDivider} = wp.components

const MollieTab = (props) => {

    return (
        <Card>
            <MollieApiModePanel
                settings={props.settings}
                mollieChanged={props.mollieChanged}
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
