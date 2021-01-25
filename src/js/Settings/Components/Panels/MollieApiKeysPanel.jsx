import {SettingCard} from "../SettingCard"

const {__} = wp.i18n
const {ExternalLink, PanelRow, TextControl} = wp.components

const MollieApiKeysPanel = (props) => {

    const handleChange = (id, value) => {
        props.mollieChanged()
        props.handleInputChange(id, value)
    }

    return (
        <SettingCard title={__('Api Keys', 'kudos-donations')}>

            <TextControl
                key={"_kudos_mollie_test_api_key"}
                label={__('Test API Key', 'kudos-donations')}
                value={props.settings._kudos_mollie_test_api_key || ''}
                placeholder={__('Begins with "test_"', 'kudos-donations')}
                disabled={props.isSaving}
                onChange={(value) => handleChange("_kudos_mollie_test_api_key", value)}
            />

            <TextControl
                key={"_kudos_mollie_live_api_key"}
                label={__('Live API Key', 'kudos-donations')}
                value={props.settings._kudos_mollie_live_api_key || ''}
                placeholder={__('Begins with "live_"', 'kudos-donations')}
                disabled={props.isSaving}
                onChange={(value) => handleChange("_kudos_mollie_live_api_key", value)}
            />

            <PanelRow>
                <ExternalLink href="https://mollie.com/dashboard/developers/api-keys">
                    {__('Get API Key(s)', 'kudos-donations')}
                </ExternalLink>
            </PanelRow>
        </SettingCard>
    )
}

export {MollieApiKeysPanel}
