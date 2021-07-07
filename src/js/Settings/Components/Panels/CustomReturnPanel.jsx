import {SettingCard} from "../SettingCard"

const CustomReturnPanel = (props) => {

    const {__} = wp.i18n
    const {TextControl, ToggleControl} = wp.components
    const {Fragment} = wp.element

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
