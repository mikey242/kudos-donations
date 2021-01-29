import {ThemePanel} from "../Panels/ThemePanel"
import {CompletedPaymentPanel} from "../Panels/CompletedPaymentPanel"
import {CustomReturnPanel} from "../Panels/CustomReturnPanel"
import {TermsPanel} from "../Panels/TermsPanel"
import {EmailReceiptsPanel} from "../Panels/EmailReceiptsPanel"

const {Card, CardDivider} = wp.components

const CustomizeTab = (props) => {

    return (
        <Card>
            <ThemePanel
                settings={props.settings}
                handleInputChange={props.handleInputChange}
            />
            <CardDivider/>
            <CompletedPaymentPanel
                settings={props.settings}
                handleInputChange={props.handleInputChange}
            />
            <CardDivider/>
            <EmailReceiptsPanel
              settings={props.settings}
              handleInputChange={props.handleInputChange}
            />
            <CardDivider/>
            <CustomReturnPanel
                settings={props.settings}
                handleInputChange={props.handleInputChange}
            />
            <CardDivider/>
            <TermsPanel
                settings={props.settings}
                handleInputChange={props.handleInputChange}
            />
        </Card>
    )
}

export {CustomizeTab}
