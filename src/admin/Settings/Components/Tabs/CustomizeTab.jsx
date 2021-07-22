import {ThemePanel} from "../Panels/ThemePanel"
import {CompletedPaymentPanel} from "../Panels/CompletedPaymentPanel"
import {CustomReturnPanel} from "../Panels/CustomReturnPanel"
import {TermsPanel} from "../Panels/TermsPanel"
import {PrivacyPanel} from "../Panels/PrivacyPanel"
import {Card, CardDivider} from "@wordpress/components"
import {SpamProtectionPanel} from "../Panels/SpamProtectionPanel"

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
            <CustomReturnPanel
                settings={props.settings}
                handleInputChange={props.handleInputChange}
            />
            <CardDivider/>
            <PrivacyPanel
                settings={props.settings}
                handleInputChange={props.handleInputChange}
            />
            <CardDivider/>
            <TermsPanel
                settings={props.settings}
                handleInputChange={props.handleInputChange}
            />
            <CardDivider/>
            <SpamProtectionPanel
                settings={props.settings}
                handleInputChange={props.handleInputChange}
            />
        </Card>
    )
}

export {CustomizeTab}
