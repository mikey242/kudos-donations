import {Dashicon} from "@wordpress/components"

const Info = ({level="info", children}) => {

    let text = 'kd-text-gray-500'
    let icon = 'info'

    switch (level) {
        case "warning":
            text = 'kd-text-orange-700'
            icon = 'warning'
            break;
    }

    return (
        <span className={"kd-flex kd-items-center kd-justify-start " + text}>
            <Dashicon className="kd-mr-1" icon={icon}/>
            <i>{children}</i>
        </span>
    )

}

export {Info}
