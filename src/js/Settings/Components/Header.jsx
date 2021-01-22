import logo from '../../../img/logo-colour.svg'
import {Btn} from "./Btn"

const {__} = wp.i18n
const {Flex, FlexItem} = wp.components

const Header = (props) => {

    let status
    let statusClass

    if (props.checkingApi) {
        statusClass = 'checking'
        status = __('checking', 'kudos-donations')
    } else if (props.settings._kudos_mollie_connected) {
        statusClass = 'connected'
        status = __('connected', 'kudos-donations')
    } else if (!props.settings._kudos_mollie_connected) {
        statusClass = 'not-connected'
        status = __('not-connected', 'kudos-donations')
    }

    return (
        <div
            className="kudos-dashboard-header kd-sticky kd-w-full kd-border-solid kd-border kd-border-gray-300 kd-z-1 kd-bg-white kd-py-3">
            <Flex className="kd-container kd-items-center kd-justify-between">
                <FlexItem className="kd-flex">
                    <img width="30" src={logo} className="kd-mr-4" alt="Kudos Logo"/>
                    <h1>{__('Kudos Donations', 'kudos-donations')}</h1>
                    <span
                        className="kudos-version kd-self-center kd-font-bold kd-rounded-lg kd-p-3 kd-ml-4 kd-border kd-border-solid kd-border-gray-400">
						{window.kudos.version}
					</span>
                </FlexItem>
                <FlexItem>
					<span
                        style={{textTransform: 'capitalize'}}
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
                </FlexItem>
            </Flex>
        </div>
    )
}

export {Header}
