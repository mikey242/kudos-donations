const { __ } = wp.i18n;
const {useEffect} = wp.element;
import logo from "../../../img/logo-colour.svg"

const KudosHeader = (props) => {

    const status = props.isMollieEdited ? 'unknown' : props.checkingApi ? 'checking' : props.apiConnected && !props.isMollieEdited ? 'connected' : !props.apiConnected ? 'not-connected' : ''

    return (
        <div className="kudos-dashboard-header">
            <div className="dashboard-wrap flex items-center justify-between">
                <div className="flex">
                    <img width="30" src={logo} alt="Kudos Logo"/>
                    <h1>{__('Kudos Settings')}</h1>
                </div>
                <div className="kudos-dashboard-header__right">
                    <span style={{textTransform: 'capitalize'}} className={
                        "kudos-api-status " + status}
                    >
                        {(status) + ( props.isMollieEdited ? '' : ' (' + props.apiMode + ')' )}
                    </span>
                    <span className="kudos-version">{kudos.version}</span>
                </div>
            </div>
        </div>
    )
}

export {KudosHeader}