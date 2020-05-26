const { __ } = wp.i18n;

const {
    PanelRow,
    ButtonGroup,
    BaseControl,
    Button,
} = wp.components;

const ButtonGroupToggle = (props) => {

    return (
        <PanelRow>

            <BaseControl
                label={__(props.label, 'kudos-donations')}
                help={__(props.help, 'kudos-donations')}
            >
                <ButtonGroup>
                    <Button
                        isPrimary
                        disabled={'test' === props.value}
                        isPressed={'test' === props.value}
                        onClick={() => props.onClick(props.option, 'test')}
                    >
                        {__('Test', 'kudos-donations')}
                    </Button>
                    <Button
                        isPrimary
                        disabled={'live' === props.value}
                        isPressed={'live' === props.value}
                        onClick={() => props.onClick(props.option, 'live')}
                    >
                        {__('Live', 'kudos-donations')}
                    </Button>
                </ButtonGroup>
            </BaseControl>
        </PanelRow>
    )
}

export {ButtonGroupToggle}