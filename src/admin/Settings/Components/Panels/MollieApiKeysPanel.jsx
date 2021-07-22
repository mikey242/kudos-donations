import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {ExternalLink, PanelRow, TextControl} from "@wordpress/components"

const MollieApiKeysPanel = (props) => {

    const handleChange = (id, value) => {
        props.mollieChanged()
        props.handleInputChange(id, value)
    }

    const vendorMollie = props.settings._kudos_vendor_mollie

    return (
        <SettingCard title={__('API keys', 'kudos-donations')}>

            <TextControl
                key={"_kudos_mollie_test_api_key"}
                type={'password'}
                onFocus={e => e.target.type = 'text'}
                onBlur={e => e.target.type = 'password'}
                label={__('Test key', 'kudos-donations')}
                value={vendorMollie['test_key'] || ''}
                placeholder={__('Begins with "test_"', 'kudos-donations')}
                disabled={props.isSaving}
                onChange={(value) => handleChange('_kudos_vendor_mollie', {...vendorMollie, test_key: value })}
            />

            <TextControl
                key={"_kudos_mollie_live_api_key"}
                type={'password'}
                onFocus={e => e.target.type = 'text'}
                onBlur={e => e.target.type = 'password'}
                label={__('Live key', 'kudos-donations')}
                value={vendorMollie['live_key'] || ''}
                placeholder={__('Begins with "live_"', 'kudos-donations')}
                disabled={props.isSaving}
                onChange={(value) => handleChange('_kudos_vendor_mollie', {...vendorMollie, live_key: value })}
            />

            <PanelRow>
                <ExternalLink href="https://mollie.com/dashboard/developers/api-keys">
                    {__('Get API key(s)', 'kudos-donations')}
                </ExternalLink>
            </PanelRow>
        </SettingCard>
    )
}

export {MollieApiKeysPanel}
