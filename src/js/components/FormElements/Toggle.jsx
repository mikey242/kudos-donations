const { __ } = wp.i18n;

const {
    PanelRow,
    ToggleControl
} = wp.components;

const Toggle = (props) => {
    return (
        <PanelRow>
            <ToggleControl
                label={__(props.label, 'kudos-donations')}
                help={__(props.help, 'kudos-donations')}
                key={"key_" + props.id}
                checked={props.value || ''}
                onChange={() => props.onChange(props.id, !props.value)}
            />
        </PanelRow>
    )
};

export {Toggle};