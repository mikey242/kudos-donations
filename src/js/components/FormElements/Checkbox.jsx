const { __ } = wp.i18n;

const {
    PanelRow,
    CheckboxControl
} = wp.components;

const Checkbox = props => {

    return (
        <PanelRow>
            <CheckboxControl
                key={"key_" + props.id}
                heading={__(props.heading, 'kudos-donations')}
                label={__(props.label, 'kudos-donations')}
                help={__(props.help, 'kudos-donations')}
                checked={props.value || ''}
                onChange={(value) => props.onChange(props.id, value)}
            />
        </PanelRow>
    )
}

export {Checkbox};