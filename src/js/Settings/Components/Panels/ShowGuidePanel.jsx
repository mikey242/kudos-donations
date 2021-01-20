const { __ } = wp.i18n;
const { PanelBody, Button, BaseControl } = wp.components;

const ShowGuidePanel = ( { handleInputChange } ) => {

    return (
        <PanelBody
            title={ __( 'Welcome guide', 'kudos-donations' ) }
            initialOpen={ false }
        >
            <BaseControl
                id="export-1"
                help={__( 'Opens the guide that was displayed when Kudos Donations was first installed.', 'kudos-donations' )}
            >
                <Button
                    isLink
                    onClick={ () => {
                        handleInputChange('_kudos_show_intro', true);
                    } }
                >
                    { __( 'Show welcome guide', 'kudos-donations' ) }
                </Button>
            </BaseControl>
        </PanelBody>
    );
};

export { ShowGuidePanel };
