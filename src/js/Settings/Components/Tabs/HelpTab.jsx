import {HelpPanel} from "../Panels/HelpPanel"


const {Card} = wp.components

const HelpTab = (props) => {

    return (
        <Card>
            <HelpPanel
                updateSetting={props.updateSetting}
            />
        </Card>
    )
}

export {HelpTab}
