import axios from 'axios'
import {SettingCard} from "../SettingCard"
import {ButtonIcon} from "../ButtonIcon"
import {__} from "@wordpress/i18n"
import {Button, PanelRow, TextControl} from "@wordpress/components"
import {useState} from "@wordpress/element"

const TestEmailPanel = (props) => {

    const [buttonDisabled, setButtonDisabled] = useState(true)
    const [email, setEmail] = useState('')
    const [isBusy, setIsBusy] = useState(false)

    const handleChange = (value) => {
        setEmail(value)
        setButtonDisabled(!validateEmail(value))
    }

    const validateEmail = (email) => {
        const emailReg = /^[\w-.]+@([\w-]+\.)+[\w-]{2,6}$/
        return emailReg.test(email)
    }

    const sendTest = (email) => {

        setIsBusy(true)

        if (!validateEmail(email)) {
            setIsBusy(false)
            props.showNotice(
                __('Invalid email address', 'kudos-donations')
            )
            return
        }

        // Perform Post request
        axios
            .post(
                window.kudos.sendTestUrl,
                {email},
                {
                    headers: {
                        // eslint-disable-next-line no-undef
                        'X-WP-Nonce': wpApiSettings.nonce,
                        'Content-Type': 'application/json'
                    },
                }
            )
            .then(response => {
                props.showNotice(response.data.data)
                setIsBusy(false)
            })
            .catch(error => {
                props.showNotice(error.response.statusText)
                setIsBusy(false)
            })
    }

    return (
        <SettingCard title={__('Send test email', 'kudos-donations')}>

            <TextControl
                label={__('Email address', 'kudos-donations')}
                help={__(
                    'Make sure you save any changes beforehand.',
                    'kudos-donations'
                )}
                type={'text'}
                value={email}
                placeholder={__(
                    'user@domain.com',
                    'kudos-donations'
                )}
                disabled={props.disabled}
                onChange={(email) => handleChange(email)}
            />

            <PanelRow>
                <Button
                    isSecondary
                    disabled={buttonDisabled || isBusy}
                    isBusy={isBusy}
                    icon={(<ButtonIcon icon="envelope"/>)}
                    onClick={() => {
                        sendTest(email)
                    }}
                >
                    {isBusy ? __('Sending', 'kudos-donations') : __('Send', 'kudos-donations')}
                </Button>
            </PanelRow>

        </SettingCard>
    )
}

export {TestEmailPanel}
