const { __ } = wp.i18n;
import logo from "../../img/logo-colour.svg"

const KudosHeader = (props) => {
    return (
        <div className="kudos-dashboard-header">
            <div className="dashboard-wrap flex items-center justify-between">
                <div className="flex">
                    <img width="30" src={logo} alt="Kudos Logo"/>
                    <h1>{__('Kudos Settings')}</h1>
                </div>
                <div className="kudos-dashboard-header__right">
                    <span className={
                        "kudos-api-status " + (props.checkingApi ? '' : props.apiConnected ? 'connected' : 'not-connected')}
                    >
                        {props.apiConnected ? 'Connected' : 'Not Connected'}
                    </span>
                    <span className="kudos-version">{kudos.version}</span>
                </div>
            </div>
        </div>
    )
}

export {KudosHeader}