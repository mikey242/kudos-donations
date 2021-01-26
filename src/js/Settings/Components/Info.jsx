const {Dashicon} = wp.components

const Info = (props) => {

    return (
        <div className="kudos-admin-info kd-text-gray-500 kd-flex kd-items-center kd-justify-start">
            <Dashicon icon={props.icon ?? "info"}/>
            <i>{props.children}</i>
        </div>
    )

}

export {Info}
