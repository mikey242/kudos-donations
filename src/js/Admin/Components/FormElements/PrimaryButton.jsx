const { __ } = wp.i18n;

const {
    PanelRow,
    Button
} = wp.components;

const PrimaryButton = props => {

    return (
        <PanelRow
            className={props.className}
        >
            <Button
                isPrimary
                disabled={props.disabled}
                isBusy={props.isBusy}
                onClick={props.onClick}
            >
                {props.label}
            </Button>
        </PanelRow>
    )
}

export {PrimaryButton};