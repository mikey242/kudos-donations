import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {TextControl, ToggleControl} from "@wordpress/components"
import {Fragment} from "@wordpress/element"

const CustomReturnPanel = (props) => {

    return (
        <SettingCard title={__('Custom return URL', 'kudos-donations')}>
            <ToggleControl
                label={__('Use custom return URL', 'kudos-donations')}
                help={__(
                    'After payment the customer is returned to the page where they clicked on the donation button. To use a different return URL, enable this option.',
                    'kudos-donations'
                )}
                checked={props.settings._kudos_custom_return_enable || ''}
                onChange={() => props.handleInputChange("_kudos_custom_return_enable", !props.settings._kudos_custom_return_enable)}
            />

            {props.settings._kudos_custom_return_enable ?
                <Fragment>
                    <br/>
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
                </Fragment>
                : ''}
        </SettingCard>
    )
}

export
{
    CustomReturnPanel
}
