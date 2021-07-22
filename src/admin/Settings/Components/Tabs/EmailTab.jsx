import {EmailCustomPanel} from "../Panels/EmailCustomPanel"
import {TestEmailPanel} from "../Panels/TestEmailPanel"
import {EmailServerPanel} from "../Panels/EmailServerPanel"
import {EmailEncryptionPanel} from "../Panels/EmailEncryptionPanel"
import {EmailAuthenticationPanel} from "../Panels/EmailAuthenticationPanel"
import {EmailFromPanel} from "../Panels/EmailFromPanel"
import {EmailReceiptsPanel} from "../Panels/EmailReceiptsPanel"
import {Card, CardDivider} from "@wordpress/components"
import {Fragment} from "@wordpress/element"

const EmailTab = (props) => {

    return (
        <Card>
            <EmailReceiptsPanel
                settings={props.settings}
                handleInputChange={props.handleInputChange}
            />
            <Fragment>
                <CardDivider/>
                <TestEmailPanel
                    handleInputChange={props.handleInputChange}
                    showNotice={props.showNotice}
                />
                <CardDivider/>
                <EmailCustomPanel
                    settings={props.settings}
                    handleInputChange={props.handleInputChange}
                />
                {props.settings._kudos_smtp_enable ?
                    <Fragment>
                        <CardDivider/>
                        <EmailServerPanel
                            settings={props.settings}
                            handleInputChange={props.handleInputChange}
                        />
                        <CardDivider/>
                        <EmailEncryptionPanel
                            settings={props.settings}
                            handleInputChange={props.handleInputChange}
                        />
                        <CardDivider/>
                        <EmailAuthenticationPanel
                            settings={props.settings}
                            handleInputChange={props.handleInputChange}
                        />
                        <CardDivider/>
                        <EmailFromPanel
                            settings={props.settings}
                            handleInputChange={props.handleInputChange}
                        />
                    </Fragment>
                : ''}
            </Fragment>
        </Card>
    )
}

export
{
    EmailTab
}
