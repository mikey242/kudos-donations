import {Btn} from "../Btn"

const {__} = wp.i18n
const {PanelBody, BaseControl, ButtonGroup, PanelRow} = wp.components

const MollieApiModePanel = (props) => {

    const handleChange = (id, value) => {
        props.mollieChanged()
        props.handleInputChange(id, value)
    }

    const selected = props.settings._kudos_mollie_api_mode

    return (
        <PanelBody>

            <BaseControl
                id="_kudos_mollie_api_mode"
                label={__('Mode', 'kudos-donations')}
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
                                handleChange('_kudos_mollie_api_mode', 'test')
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
                                handleChange('_kudos_mollie_api_mode', 'live')
                            }
                        >
                            {'Live'}
                        </Btn>
                    </ButtonGroup>
                </PanelRow>
            </BaseControl>

        </PanelBody>
    )
}

export {MollieApiModePanel}
