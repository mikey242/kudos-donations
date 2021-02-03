const {CardBody} = wp.components
const {applyFilters} = wp.hooks

const SettingCard = (props) => {

    let filtered = applyFilters('kudos.settings.settingCard.' + props.id, {...props})

    return (
        <CardBody size="medium">
            <h3 className="kd-mb-5">{filtered.title}</h3>
            {filtered.children}
        </CardBody>
    )
}

export {SettingCard}
