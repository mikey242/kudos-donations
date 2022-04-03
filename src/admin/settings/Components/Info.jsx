import {Dashicon} from "@wordpress/components"

const Info = ({level="info", children}) => {

    let text = 'text-gray-500'
    let icon = 'info'

    switch (level) {
        case "warning":
            text = 'text-orange-700'
            icon = 'warning'
            break;
    }

    return (
        <span className={"flex items-center justify-start " + text}>
            <Dashicon className="mr-1" icon={icon}/>
            <i>{children}</i>
        </span>
    )

}

export {Info}
