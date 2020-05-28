const { __ } = wp.i18n;

const {
    PanelBody,
} = wp.components;

const {
    Fragment,
    useState
} = wp.element;

import {Toggle} from "../FormElements/Toggle"
import {TextInput} from "../FormElements/TextInput"
import {RadioButtons} from "../FormElements/RadioButtons"
import {Checkbox} from "../FormElements/Checkbox"
import {PrimaryButton} from "../FormElements/PrimaryButton"

const EmailSettingsPanel = props => {

    const [isEdited, setIsEdited] = useState(false);

    const handleChange = (option, value) => {
        setIsEdited(true)
        props.handleInputChange(option, value);
    }

    let optionalForm = (
        <Fragment>
            <TextInput
                id='_kudos_smtp_host'
                label='Host'
                help="Your email server's hostname"
                value={props._kudos_smtp_host}
                placeholder='mail.host.com'
                onChange={handleChange}
            />
            <RadioButtons
                id='_kudos_smtp_encryption'
                label='Encryption'
                help='Choose your encryption type'
                selected={props._kudos_smtp_encryption}
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
                value={props._kudos_smtp_autotls}
                onChange={props.updateSetting}
            />
            <TextInput
                id='_kudos_smtp_username'
                label='Username'
                help="This is usually an email address"
                value={props._kudos_smtp_username}
                placeholder='user@domain.com'
                onChange={handleChange}
            />
            <TextInput
                id='_kudos_smtp_password'
                label='Password'
                type="password"
                help=""
                value={props._kudos_smtp_password}
                placeholder='*****'
                onChange={handleChange}
            />
            <TextInput
                id='_kudos_smtp_port'
                label='Port'
                help=""
                value={props._kudos_smtp_port}
                placeholder='587'
                onChange={handleChange}
            />

            <PrimaryButton
                label='Save'
                isBusy={props.isSaving}
                disabled={!isEdited || props.isSaving}
                onClick={() => {
                    props.updateSetting('_kudos_smtp_host', props._kudos_smtp_host, true)
                    props.updateSetting('_kudos_smtp_username', props._kudos_smtp_username, true)
                    props.updateSetting('_kudos_smtp_password', props._kudos_smtp_password, true)
                    props.updateSetting('_kudos_smtp_port', props._kudos_smtp_port, true)
                    setIsEdited(false)
                }}
            />
        </Fragment>
    )

    if(!props._kudos_smtp_enable) {
        optionalForm = '';
    }

    return (
        <PanelBody
            title={__('Email Settings')}
            initialOpen={false}
        >

            <Toggle
                id='_kudos_smtp_enable'
                label={'Use custom email settings'}
                help={'Enable this to use your own SMTP server settings.'}
                value={props._kudos_smtp_enable}
                onChange={props.updateSetting}
            />

            { optionalForm }

        </PanelBody>
    )
}

export {EmailSettingsPanel}