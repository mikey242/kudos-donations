const { __ } = wp.i18n;

const {
    PanelRow,
    BaseControl,
    ButtonGroup,
    Button
} = wp.components;

const RadioImage = props => {

    return (
        <PanelRow>

            <BaseControl
                label={__(props.label, 'kudos-donations')}
                help={__(props.help, 'kudos-donations')}
                className={"components-kudos-radio-buttons" + (props.className ? ' ' + props.className : '')}
            >
                <ButtonGroup>
                    {props.children.map((child, index) => {
                        return (
                            <Button
                                isPrimary={props.isPrimary}
                                key={child.value + '-' + index}
                                disabled={child.value === props.value}
                                isPressed={child.value === props.value}
                                onClick={() => props.onClick(props.id, child.value)}
                            >
                                {child.content}
                            </Button>
                        )
                    })}

                </ButtonGroup>
            </BaseControl>
        </PanelRow>
    )
}

export {RadioImage};