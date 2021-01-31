import {Info} from "../Info"
import {SettingCard} from "../SettingCard"

const {__} = wp.i18n
const {TextControl, ToggleControl} = wp.components
const {Fragment} = wp.element

const CompletedPaymentPanel = (props) => {
    return (
        <SettingCard title={__('Completed payment', 'kudos-donations')}>
                <ToggleControl
                    label={__(
                        'Show pop-up message when payment complete.',
                        'kudos-donations'
                    )}
                    help={__(
                        'Enable this to show a pop-up thanking the customer for their donation.',
                        'kudos-donations'
                    )}
                    checked={props.settings._kudos_return_message_enable || ''}
                    onChange={() => props.handleInputChange("_kudos_return_message_enable", !props.settings._kudos_return_message_enable)}
                />

            {props.settings._kudos_return_message_enable ?

                <Fragment>

                    <TextControl
                        label={__('Title', 'kudos-donations')}
                        type={'text'}
                        value={props.settings._kudos_return_message_title || ''}
                        disabled={props.isSaving}
                        onChange={(value) => props.handleInputChange("_kudos_return_message_title", value)}
                    />
                    <br/>
                    <TextControl
                        label={__('Text', 'kudos-donations')}
                        type={'text'}
                        value={props.settings._kudos_return_message_text || ''}
                        placeholder={__(
                            'Button label',
                            'kudos-donations'
                        )}
                        disabled={props.isSaving}
                        onChange={(value) => props.handleInputChange("_kudos_return_message_text", value)}
                    />

                    <Info>
                        {__('You can use the following variables in the above fields: {{name}}, {{email}}, {{value}}, {{campaign}}', 'kudos-donations')}
                    </Info>

                </Fragment>

                : ''}

        </SettingCard>
    )
}

export {CompletedPaymentPanel}
