const { __ } = wp.i18n;

const {
    PanelRow,
    Button
} = wp.components;

const PrimaryButton = props => {

    return (
        <PanelRow>
            <Button
                isPrimary
                disabled={props.disabled}
                isBusy={props.isBusy}
                onClick={props.onClick}
            >
                {__(props.label, 'kudos-donations')}
            </Button>

            {props.children}

        </PanelRow>
    )
}

export {PrimaryButton};