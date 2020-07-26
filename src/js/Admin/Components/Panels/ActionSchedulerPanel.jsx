import { Toggle } from '../FormElements/Toggle';

const { __ } = wp.i18n;
const { PanelBody } = wp.components;

const ActionSchedulerPanel = ( props ) => {
    return (
        <PanelBody
            title={ __( 'Action Scheduler', 'kudos-donations' ) }
            initialOpen={ false }
        >
            <Toggle
                id="_kudos_action_scheduler"
                label={ __( 'Enable action scheduler', 'kudos-donations' ) }
                help={ __(
                    'In most cases this should be left on, only disable this if you are experiencing issues with emails and invoices not working.',
                    'kudos-donations'
                ) }
                value={ props.settings._kudos_action_scheduler }
                onChange={ props.handleInputChange }
            />
        </PanelBody>
    );
};

export { ActionSchedulerPanel };
