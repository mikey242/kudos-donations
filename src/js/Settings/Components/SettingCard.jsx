const {CardBody} = wp.components
const {Fragment} = wp.element
const {applyFilters} = wp.hooks

const SettingCard = (props) => {


    return (
        applyFilters('kudos.settings.settingCard.' + props.id,
            <Fragment>
                <CardBody size="medium">
                    <header className={"kd-mb-6"}>
                        <h3 className="kd-mb-5">{props.title}</h3>
                        {/*<p className={"kd-mb-6 kd-text-lg kd-text-gray-500"}>{props.description}</p>*/}
                    </header>
                    {props.children}
                </CardBody>
            </Fragment>,
            props)
    )
}

export {SettingCard}
