const {CardBody} = wp.components

const SettingCard = ({ title, children }) => {

    return (
        <CardBody size="medium">
            <h4 className="kd-mb-5">{title}</h4>
            { children }
        </CardBody>
    )
}

export {SettingCard}
