const { __ } = wp.i18n;
const { CheckboxControl, PanelBody, PanelRow } = wp.components;

const SubscriptionPanel = (props ) => {

    return (
        <PanelBody
            title={ __( 'Subscriptions', 'kudos-donations' ) }
            initialOpen={ false }
        >

            <PanelRow>

                <CheckboxControl
                    label="Enable"
                    checked={ props.settings._kudos_subscription_enabled || '' }
                    onChange={ ( value ) => props.handleInputChange( '_kudos_subscription_enabled', value ) }
                />

            </PanelRow>

        </PanelBody>
    );
};

export { SubscriptionPanel };
