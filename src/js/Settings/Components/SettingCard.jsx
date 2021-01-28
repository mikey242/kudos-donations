const {CardBody} = wp.components

const SettingCard = ({ title, children }) => {

    return (
        <CardBody size="medium">
            <p><strong>{title}</strong></p>
            { children }
        </CardBody>
    )
}

export {SettingCard}
