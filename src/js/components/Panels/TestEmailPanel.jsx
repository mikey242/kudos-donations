const { __ } = wp.i18n;

const {
    PanelRow,
    PanelBody,
    BaseControl,
    Button,
} = wp.components;

const {
    useState
} = wp.element;

const TestEmailPanel = props => {

    const [isEdited, setIsEdited] = useState(false);
    const [email, setEmail] = useState('');

    const handleChange = (e) => {
        setIsEdited(true)
        setEmail(e.target.value)
    }

    return (
        <PanelBody
            title={__('Send Test', 'kudos-mollie')}
        >

        <PanelRow>
            <BaseControl
                label={__(props.label, 'kudos-donations')}
                help={__(props.help, 'kudos-donations')}
            >
                <input
                    key={"key_test_email_text"}
                    type={"text"}
                    value={email}
                    placeholder={__(props.placeholder, 'kudos-donations')}
                    disabled={props.disabled}
                    onChange={(e)=>handleChange(e)}
                />
            </BaseControl>
        </PanelRow>

        <PanelRow>
            <Button
                isPrimary
                disabled={!isEdited || props.isSaving}
                onClick={() => {props.showNotice(email)}}
            >
                {__('Send', 'kudos-donations')}
            </Button>
        </PanelRow>

        </PanelBody>
    )
}

export {TestEmailPanel}