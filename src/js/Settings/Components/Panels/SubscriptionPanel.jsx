const { __ } = wp.i18n;
const { PanelBody, ToggleControl, PanelRow } = wp.components;

const SubscriptionPanel = (props ) => {

    return (
        <PanelBody
            title={ __( 'Subscriptions', 'kudos-donations' ) }
            initialOpen={ false }
        >

            <PanelRow>

                <ToggleControl
                    label="Enable"
                    help={ __(
                        'Allows donors to donate automatically at regular intervals.',
                        'kudos-donations'
                    ) }
                    checked={ props.settings._kudos_subscription_enabled || '' }
                    onChange={ ( value ) => props.handleInputChange( '_kudos_subscription_enabled', value ) }
                />

            </PanelRow>

        </PanelBody>
    );
};

export { SubscriptionPanel };
