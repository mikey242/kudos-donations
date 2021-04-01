import {Btn} from "./Btn"

const {__} = wp.i18n
const {applyFilters} = wp.hooks

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
            className="kudos-dashboard-header kd-sticky kd-border-solid kd-border kd-border-gray-300 kd-z-1050 kd-bg-white kd-py-3">
            <div className="kd-container kd-flex kd-items-center kd-justify-between">
                <div className="kd-flex">
                    <a title={__('Visit Kudos Donations')} className="kd-flex kd-mr-4 kd-logo-animate" href="https://kudosdonations.com"
                       target="_blank">
                        <svg
                            className="kd-logo kd-origin-center kd-rotate-0 kd-transition-transform kd-duration-500 kd-ease-in-out"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 555 449" style={{width: '30px'}}>
                            <path className="kd-logo-line" fill="#2ec4b6"
                                  d="M0-.003h130.458v448.355H.001z"/>
                            <path
                                className="kd-logo-heart kd-origin-center kd-transition-transform kd-duration-500 kd-ease-in-out"
                                fill="#ff9f1c"
                                d="M489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/>
                        </svg>
                    </a>
                    <h1>{__('Kudos Donations', 'kudos-donations')}</h1>
                    <span
                        className="kudos-version kd-self-center kd-font-bold kd-rounded-lg kd-p-3 kd-ml-4 kd-border kd-border-solid kd-border-gray-400">
						{applyFilters('kudos.settings.headerVersion', window.kudos.version)}
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
