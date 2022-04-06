import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {RadioControl, CheckboxControl} from "@wordpress/components"

const EmailEncryptionPanel = (props) => {

    return (
        <SettingCard title={__('Encryption', 'kudos-donations')}>
            <RadioControl
                id="_kudos_smtp_encryption"
                help={__(
                    'Choose your encryption type. Always use TLS or SSL if available.',
                    'kudos-donations'
                )}
                selected={props.settings._kudos_smtp_encryption}
                options={
                    [
                        {label: __('None', 'kudos-donations'), value: 'none'},
                        {label: __('SSL', 'kudos-donations'), value: 'ssl'},
                        {label: __('TLS', 'kudos-donations'), value: 'tls'},
                    ]
                }
                onChange={(value) => props.handleInputChange('_kudos_smtp_encryption', value)}
            />
            <br/>
            <CheckboxControl
                label={__('Auto TLS', 'kudos-donations')}
                help={__(
                    'In most cases you will want this enabled. Disable for troubleshooting.',
                    'kudos-donations'
                )}
                checked={props.settings._kudos_smtp_autotls || ''}
                onChange={(value) => props.handleInputChange('_kudos_smtp_autotls', value)}
            />
        </SettingCard>
    )
}

export { EmailEncryptionPanel }