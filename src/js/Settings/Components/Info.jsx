const {PanelRow, Dashicon} = wp.components

const Info = (props) => {

    return (

        <PanelRow>

            <div className="kudos-admin-info kd-text-gray-500 kd-flex kd-items-center kd-justify-center">
                <Dashicon icon={props.icon ?? "info"}/>
                <i>{props.children}</i>
            </div>

        </PanelRow>

    )

}

export {Info}
