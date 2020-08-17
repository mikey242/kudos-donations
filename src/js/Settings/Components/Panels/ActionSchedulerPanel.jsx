const {__} = wp.i18n;
const {PanelBody, ToggleControl} = wp.components;

const ActionSchedulerPanel = ( props ) => {
    return (

        <PanelBody
            title={ __( 'Action Scheduler', 'kudos-donations' ) }
            initialOpen={ false }
        >

            <ToggleControl
                label={ __( 'Enable action scheduler', 'kudos-donations' ) }
                help={ __(
                    'In most cases this should be left on, only disable this if you are experiencing issues with emails and invoices not working.',
                    'kudos-donations'
                ) }
                checked={ props.settings._kudos_action_scheduler || '' }
                onChange={ () => props.handleInputChange( "_kudos_action_scheduler", ! props.settings._kudos_action_scheduler ) }
            />

        </PanelBody>
    );
};

export { ActionSchedulerPanel };
