import {Info} from "../Info"
import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {RadioControl, TextControl} from "@wordpress/components"
import {Fragment} from "@wordpress/element"

const CompletedPaymentPanel = (props) => {

    return (
        <SettingCard title={__('Completed payment', 'kudos-donations')}>

            <RadioControl
                help={__('When the donor returns to your website after completing the payment what do you want to happen?', 'kudos-donations')}
                selected={props.settings._kudos_completed_payment || 'message'}
                options={[
                    {label: __('Pop-up message', 'kudos-donations'), value: 'message'},
                    {label: __('Custom return URL', 'kudos-donations'), value: 'url'}
                ]}
                onChange={(value) => {
                    props.settings._kudos_completed_payment = value
                    props.handleInputChange('_kudos_completed_payment', props.settings._kudos_completed_payment)
                }}
            />

            {props.settings._kudos_completed_payment === 'message' ?

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

                :
                <TextControl
                    label={__('URL', 'kudos-donations')}
                    help={__(
                        'e.g https://mywebsite.com/thanks',
                        'kudos-donations'
                    )}
                    type={'text'}
                    value={props.settings._kudos_custom_return_url || ''}
                    disabled={props.isSaving}
                    onChange={(value) => props.handleInputChange("_kudos_custom_return_url", value)}
                />
            }

        </SettingCard>
    )
}

export {CompletedPaymentPanel}
