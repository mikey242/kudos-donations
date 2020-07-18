const {
    PanelRow,
    BaseControl,
    TextControl
} = wp.components;

const TextInput = props => {

    return (
        <PanelRow>
            <BaseControl
                label={props.label}
                help={props.help}
            >
                <TextControl
                    key={"key_" + props.id}
                    id={props.id}
                    type={props.type || "text"}
                    value={props.value || ""}
                    placeholder={props.placeholder}
                    disabled={props.disabled}
                    onChange={ (value) => props.onChange(props.id, value) }
                />
            </BaseControl>
        </PanelRow>
    )
}

export {TextInput};