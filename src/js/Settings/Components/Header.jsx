import logo from '../../../img/logo-colour.svg'
import {Btn} from "./Btn"

const {__} = wp.i18n

const Header = (props) => {

    let status
    let statusClass

    if (props.checkingApi) {
        statusClass = 'checking'
        status = __('Checking', 'kudos-donations')
    } else if (props.settings._kudos_mollie_connected) {
        statusClass = 'connected'
        status = __('Mollie connected', 'kudos-donations')
    } else if (!props.settings._kudos_mollie_connected) {
        statusClass = 'not-connected'
        status = __('Not connected', 'kudos-donations')
    }

    return (
        <div
            className="kudos-dashboard-header kd-sticky kd-border-solid kd-border kd-border-gray-300 kd-z-1050 kd-bg-white kd-py-3">
            <div className="kd-container kd-flex kd-items-center kd-justify-between">
                <div className="kd-flex">
                    <img width="30" src={logo} className="kd-mr-4" alt="Kudos Logo"/>
                    <h1>{__('Kudos Donations', 'kudos-donations')}</h1>
                    <span
                        className="kudos-version kd-self-center kd-font-bold kd-rounded-lg kd-p-3 kd-ml-4 kd-border kd-border-solid kd-border-gray-400">
						{window.kudos.version}
					</span>
                </div>
                <div className="kd-flex kd-items-center">
					<span
                        className={'kudos-api-status ' + statusClass + ' kd-text-gray-600 kd-mr-4'}
                    >
						{status}
					</span>
                    <Btn
                        isPrimary
                        disabled={
                            props.isSaving ||
                            !props.isEdited
                        }
                        isBusy={
                            props.isSaving ||
                            props.checkingApi
                        }
                        onClick={props.updateAll}
                    >
                        {__('Save', 'kudos-donations')}
                    </Btn>
                </div>
            </div>
        </div>
    )
}

export {Header}
