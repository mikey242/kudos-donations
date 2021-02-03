const {CardBody} = wp.components
const {applyFilters} = wp.hooks

const SettingCard = ({ title, children, id }) => {

    let filtered = applyFilters('kudos.settings.settingCard.' + id, {title: title, children: children})

    return (
        <CardBody size="medium">
            <h3 className="kd-mb-5">{filtered.title}</h3>
            {filtered.children}
        </CardBody>
    )
}

export {SettingCard}
