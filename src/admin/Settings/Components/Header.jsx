import {Btn} from "./Btn"
import {KudosLogo} from "../../../common/components/KudosLogo"
import {__} from "@wordpress/i18n"
import {applyFilters} from "@wordpress/hooks"

const Header = (props) => {

    let status
    let statusClass

    if (props.checkingApi) {
        statusClass = 'checking'
        status = __('Checking', 'kudos-donations')
    } else if (props.settings._kudos_vendor_mollie['connected']) {
        statusClass = 'connected'
        status = __('Mollie connected', 'kudos-donations')
    } else if (!props.settings._kudos_vendor_mollie['connected']) {
        statusClass = 'not-connected'
        status = __('Not connected', 'kudos-donations')
    }

    return (
        <div
            className="kudos-dashboard-header sticky border-solid border-1 border-gray-300 z-1050 bg-white py-3">
            <div className="w-[768px] mx-auto flex items-center justify-between">
                <div className="flex">
                    <a title={__('Visit Kudos Donations')} className="flex mr-4 logo-animate" href="https://kudosdonations.com"
                       target="_blank">
                        <KudosLogo/>
                    </a>
                    <h1>{__('Kudos Donations', 'kudos-donations')}</h1>
                    <span
                        className="kudos-version self-center font-bold rounded-lg p-3 ml-4 border-1 border-solid border-gray-400">
						{applyFilters('kudos.settings.headerVersion', window.kudos.version)}
					</span>
                </div>
                <div className="flex items-center">
					<span
                        className={'kudos-api-status ' + statusClass + ' text-gray-600 mr-4'}
                    >
						{status}
					</span>
                    <Btn
                        isPrimary
                        disabled={
                            props.isAPISaving ||
                            !props.isEdited
                        }
                        isBusy={
                            props.isAPISaving ||
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
