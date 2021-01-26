const {Dashicon} = wp.components

const Info = (props) => {

    return (
        <div className="kudos-admin-info kd-text-gray-500 kd-flex kd-items-center kd-justify-start">
            <Dashicon className="kd-mr-1" icon={props.icon ?? "info"}/>
            <i>{props.children}</i>
        </div>
    )

}

export {Info}
