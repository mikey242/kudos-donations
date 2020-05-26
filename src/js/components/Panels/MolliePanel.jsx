const { __ } = wp.i18n;

const {
    PanelRow,
    PanelBody,
    Button,
    ExternalLink
} = wp.components;

const {
    useState
} = wp.element;

import {TextInput} from "../FormElements/TextInput";
import {ButtonGroupToggle} from "../FormElements/ButtonGroupToggle"

const MolliePanel = props => {

    const [isEdited, setIsEdited] = useState(false);

    const handleChange = (e) => {
        setIsEdited(true)
        props.handleInputChange(e);
    }

    console.log(isEdited);

    return (
        <PanelBody
            title={__('Mollie')}
        >
            <ButtonGroupToggle
                label='Mollie API Mode'
                help='When using Kudos Donations for the first time, the payment mode is set to "Test". Check that the configuration is working correctly. Once you are ready to receive live payments you can switch the mode to "Live".'
                option='_kudos_mollie_api_mode'
                value={props.apiMode}
                onClick={props.updateSetting}
            >
            </ButtonGroupToggle>

            <TextInput
                id='_kudos_mollie_test_api_key'
                label="Test API Key"
                value={props.testKey}
                placeHolder='Mollie Test API Key'
                disabled={props.isSaving || props.apiMode !== 'test'}
                onChange={handleChange}
            />

            <TextInput
                id='_kudos_mollie_live_api_key'
                label="Mollie Live API Key"
                value={props.liveKey}
                placeHolder='Mollie Live API Key'
                disabled={props.isSaving || props.apiMode !== 'live'}
                onChange={handleChange}
            />

            <PanelRow>
                <Button
                    isPrimary
                    disabled={!isEdited || props.isSaving}
                    onClick={() => {
                        props.updateSetting('_kudos_mollie_test_api_key', props.testKey)
                        props.updateSetting('_kudos_mollie_live_api_key', props.liveKey)
                        props.checkApiKey()
                    }}
                >
                    {__('Save', 'kudos-donations')}
                </Button>

                <ExternalLink href="https://mollie.com/dashboard/developers/api-keys">
                    {__('Get API Key(s)', 'kudos-donations')}
                </ExternalLink>
            </PanelRow>

        </PanelBody>
    )
}

export {MolliePanel}