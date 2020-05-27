import axios from "axios"
import {PrimaryButton} from "../FormElements/PrimaryButton"

const { __ } = wp.i18n;

const {
    PanelRow,
    PanelBody,
    BaseControl,
} = wp.components;

const {
    useState
} = wp.element;

const TestEmailPanel = props => {

    const [isEdited, setIsEdited] = useState(false);
    const [email, setEmail] = useState('');
    const [isBusy, setIsBusy] = useState(false);

    const handleChange = (e) => {
        setIsEdited(true)
        setEmail(e.target.value)
    }

    const sendTest = (email) => {
        setIsBusy(true);

        // Perform Post request
        axios.post(kudos.sendTestUrl, {
            'email': email,
        },{
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce
            }
        }
        ).then(response => {
            props.showNotice(response.data.data);
            setIsBusy(false);
        }).catch(error => {
            console.log(error)
        })
    }

    return (

        <PanelBody
            title={__('Send Test Email', 'kudos-donations')}
            initialOpen={false}
        >

        <PanelRow>
            <BaseControl
                label={__('Email address', 'kudos-donations')}
                help={__('Make sure you save any changes beforehand.', 'kudos-donations')}
            >
                <input
                    key={"key_test_email_text"}
                    type={"text"}
                    value={email}
                    placeholder={__('user@domain.com', 'kudos-donations')}
                    disabled={props.disabled}
                    onChange={(e)=>handleChange(e)}
                />
            </BaseControl>
        </PanelRow>

        <PrimaryButton
            label='Send'
            disabled={!isEdited || isBusy}
            isBusy={isBusy}
            onClick={() => {
                sendTest(email);
            }}
        />

        </PanelBody>
    )
}

export {TestEmailPanel}