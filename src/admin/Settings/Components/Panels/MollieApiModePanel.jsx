import {Btn} from "../Btn"
import {SettingCard} from "../SettingCard"
import {ButtonIcon} from "../ButtonIcon"
import {__} from "@wordpress/i18n"
import {BaseControl, Button, ButtonGroup, PanelRow} from "@wordpress/components"
import {useState} from "@wordpress/element"

const MollieApiModePanel = (props) => {

    const [isBusy, setIsBusy] = useState(false)

    const vendorMollie = props.settings._kudos_vendor_mollie
    const selected = vendorMollie['mode']

    const handleChange = (id, value) => {
        props.mollieChanged()
        props.handleInputChange(id, value)
    }

    const refresh = () => {
        setIsBusy(true)
        props.checkApiKey(true, () => setIsBusy(false))
    }

    return (
        <SettingCard title={__('API mode', 'kudos-donations')}>

            <BaseControl
                id="_kudos_mollie_api_mode"
                help={__(
                    'When using Kudos Donations for the first time, the payment mode is set to "Test". Check that the configuration is working correctly. Once you are ready to receive live payments you can switch the mode to "Live".',
                    'kudos-donations'
                )}
                className={'components-kudos-radio-buttons components-kudos-toggle'}
            >
                <PanelRow>
                    <ButtonGroup>
                        <Btn
                            className="kd-rounded-l-sm kd-shadow-button-group"
                            isPrimary={'test' === selected}
                            isSecondary={'test' !== selected}
                            isPressed={'test' === selected}
                            onClick={() =>
                                handleChange('_kudos_vendor_mollie', {...vendorMollie, mode: 'test'})
                            }
                        >
                            {'Test'}
                        </Btn>
                        <Btn
                            className="kd-rounded-r-sm kd-shadow-button-group"
                            isPrimary={'live' === selected}
                            isSecondary={'live' !== selected}
                            isPressed={'live' === selected}
                            onClick={() =>
                                handleChange('_kudos_vendor_mollie', {...vendorMollie, mode: 'live'})
                            }
                        >
                            {'Live'}
                        </Btn>
                    </ButtonGroup>
                </PanelRow>
            </BaseControl>

            {vendorMollie.connected ?
                <BaseControl
                    help={__("Use this if you have made changes in Mollie such as enabling SEPA Direct Debit or credit card.", 'kudos-donations')}
                >
                    <Button
                        isLink
                        icon={(<ButtonIcon icon="sync" className={(isBusy ? 'kd-animate-spin' : '')}/>)}
                        onClick={() => refresh()}
                    >
                        {__('Refresh API', 'kudos-donations')}
                    </Button>
                </BaseControl>
                : ''}


        </SettingCard>
    )
}

export {MollieApiModePanel}
