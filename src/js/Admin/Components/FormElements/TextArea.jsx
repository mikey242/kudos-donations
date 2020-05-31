const { __ } = wp.i18n;

const {
    PanelRow,
    BaseControl,
    TextareaControl
} = wp.components;

const TextArea = props => {

    return (
        <PanelRow>
            <BaseControl
                label={__(props.label, 'kudos-donations')}
                help={__(props.help, 'kudos-donations')}
            >
                <TextareaControl
                    key={"key_" + props.id}
                    id={props.id}
                    type={props.type || "text"}
                    value={props.value}
                    placeholder={__(props.placeholder, 'kudos-donations')}
                    disabled={props.disabled}
                    onChange={ (value) => props.onChange(props.id, value) }
                />
            </BaseControl>
        </PanelRow>
    )
}

export {TextArea};