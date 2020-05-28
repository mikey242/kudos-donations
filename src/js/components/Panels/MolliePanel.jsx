const { __ } = wp.i18n;

const {
    PanelRow,
    PanelBody,
    ExternalLink
} = wp.components;

import {TextInput} from "../FormElements/TextInput"
import {RadioImage} from "../FormElements/RadioImage"

const MolliePanel = props => {

    const handleChange = (id, value) => {
        props.mollieChanged()
        props.handleInputChange(id, value)
    }

    return (
        <PanelBody
            title={__('Mollie', 'kudos-donations')}
            initialOpen={false}
        >

            <RadioImage
                isPrimary
                className="components-kudos-toggle"
                id="_kudos_mollie_api_mode"
                value={props.settings._kudos_mollie_api_mode}
                label={__('Mollie API Mode', 'kudos-donations')}
                help={__('When using Kudos Donations for the first time, the payment mode is set to "Test". Check that the configuration is working correctly. Once you are ready to receive live payments you can switch the mode to "Live".', 'kudos-donations')}
                onClick={handleChange}
            >
                { [
                    { value: 'test', content: 'Test' },
                    { value: 'live', content: 'Live' },
                ] }
            </RadioImage>

            <TextInput
                id='_kudos_mollie_test_api_key'
                label="Test API Key"
                value={props.settings._kudos_mollie_test_api_key}
                placeHolder='Mollie Test API Key'
                disabled={props.isSaving || props.settings._kudos_mollie_api_mode !== 'test'}
                onChange={handleChange}
            />

            <TextInput
                id='_kudos_mollie_live_api_key'
                label="Mollie Live API Key"
                value={props.settings._kudos_mollie_live_api_key}
                placeHolder='Mollie Live API Key'
                disabled={props.isSaving || props.settings._kudos_mollie_api_mode !== 'live'}
                onChange={handleChange}
            />

            <PanelRow>
                <ExternalLink href="https://mollie.com/dashboard/developers/api-keys">
                    {__('Get API Key(s)', 'kudos-donations')}
                </ExternalLink>
            </PanelRow>

        </PanelBody>
    )
}

export {MolliePanel}