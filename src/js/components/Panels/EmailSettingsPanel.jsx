const { __ } = wp.i18n;

const {
    PanelRow,
    PanelBody,
    Button
} = wp.components;

const {
    Fragment,
    useState
} = wp.element;

import {Toggle} from "../FormElements/Toggle";
import {TextInput} from "../FormElements/TextInput";
import {RadioButtons} from "../FormElements/RadioButtons";
import {Checkbox} from "../FormElements/Checkbox";

const EmailSettingsPanel = props => {

    const [isEdited, setIsEdited] = useState(false);

    const handleChange = (e) => {
        setIsEdited(true);
        props.handleInputChange(e)
    }

    let optionalForm = (
        <Fragment>
            <TextInput
                id='_kudos_smtp_host'
                label='Host'
                help="Your email server's hostname"
                value={props.host}
                placeholder='mail.host.com'
                onChange={handleChange}
            />
            <RadioButtons
                id='_kudos_smtp_encryption'
                label='Encryption'
                help='Choose your encryption type'
                selected={props.encryption}
                onChange={props.updateSetting}
            >
                { [
                    { label: __('None', 'kudos-donations'), value: 'none' },
                    { label: __('SSL', 'kudos-donations'), value: 'ssl' },
                    { label: __('TLS', 'kudos-donations'), value: 'tls' },
                ] }
            </RadioButtons>
            <Checkbox
                id='_kudos_smtp_autotls'
                heading='Auto TLS'
                label='Enable'
                help='In most cases you will want this enabled. Disable for troubleshooting.'
                value={props.autoTls}
                onChange={props.updateSetting}
            />
            <TextInput
                id='_kudos_smtp_username'
                label='Username'
                help="This is usually an email address"
                value={props.username}
                placeholder='user@domain.com'
                onChange={handleChange}
            />
            <TextInput
                id='_kudos_smtp_password'
                label='Password'
                type="password"
                help=""
                value={props.password}
                placeholder='*****'
                onChange={handleChange}
            />
            <TextInput
                id='_kudos_smtp_port'
                label='Port'
                help=""
                value={props.port}
                placeholder='587'
                onChange={handleChange}
            />
            <PanelRow>
                <Button
                    isPrimary
                    disabled={!isEdited || props.isSaving}
                    onClick={() => {
                        props.updateSetting('_kudos_smtp_host', props.host, true)
                        props.updateSetting('_kudos_smtp_username', props.username, true)
                        props.updateSetting('_kudos_smtp_password', props.password, true)
                        props.updateSetting('_kudos_smtp_port', props.port, true)
                    }}
                >
                    {__('Save', 'kudos-donations')}
                </Button>
            </PanelRow>
        </Fragment>
    )

    if(!props.enableSmtp) {
        optionalForm = '';
    }

    return (
        <PanelBody
            title={__('Email Settings')}
        >
            <Toggle
                id='_kudos_smtp_enable'
                label={'Use custom email settings'}
                help={'Enable this to use your own SMTP server settings.'}
                value={props.enableSmtp}
                onChange={props.updateSetting}
            />

            { optionalForm }

        </PanelBody>
    )
}

export {EmailSettingsPanel}