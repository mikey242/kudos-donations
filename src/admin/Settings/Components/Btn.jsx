import {Button} from "@wordpress/components"

const Btn = (props) => {

    return (

        <Button
            className={(props.isPrimary ? "kd-bg-orange-500 " : "") + props.className}
            isPrimary={props.isPrimary}
            isSecondary={props.isSecondary}
            isPressed={props.isPressed}
            isBusy={props.isBusy}
            disabled={props.disabled}
            onClick={() =>
                props.onClick()
            }
        >
            {props.children}
        </Button>

    )

}

export {Btn}