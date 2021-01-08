import { CampaignPanel } from "./CampaignPanel"

const { __, sprintf } = wp.i18n;
const { PanelBody, BaseControl, TextControl, Button } = wp.components;
const { useState } = wp.element;

const AddCampaignPanel = ( props ) => {

    const [ addFormValue, setAddFormValue ] = useState('');

    const addCampaign = ( name ) => {

        let current = props.settings._kudos_campaigns;

        // Bail if name is empty
        if( '' === name.trim() ) {
            props.showNotice(__('Campaign name empty.', 'kudos-donations'))
            return;
        }

        // Bail if duplicate found
        if(current.find( x => x.name.toLowerCase() === name.toLowerCase().trim() )) {
            props.showNotice(__('Duplicate campaign name', 'kudos-donations'))
            return;
        }

        // Add new campaign with defaults to top of array using unshift
        current.unshift({
            slug: name,
            name: name,
            modal_title: __( 'Support us!', 'kudos-donations' ),
            welcome_text: __( 'Your support is greatly appreciated and will help to keep us going.', 'kudos-donations' ),
            donation_type: 'both',
            amount_type: 'both',
            fixed_amounts: '5,10,20,50'
        })

        // Save changes and show notice
        props.updateSetting('_kudos_campaigns', current );
        props.showNotice( sprintf(__('Added campaign "%s".', 'kudos-donations'), name) )
    };

    return (
        <div>
            <PanelBody
                title={ __( 'Campaigns', 'kudos-donations' ) }
                initialOpen={ true }
            >

                <BaseControl
                    help={__("Give your campaign a unique name to identify it.", 'kudos-donations')}
                >
                    <TextControl
                        label={ __(
                            'Add campaign',
                            'kudos-donations'
                        ) }
                        id={'kudos_new_campaign'}
                        className={'kd-inline'}
                        type={ 'text' }
                        value={ addFormValue }
                        onChange={ (newValue) => setAddFormValue( newValue ) }
                    />

                    <Button
                        isPrimary
                        isSmall
                        onClick={
                            () => addCampaign(document.getElementById('kudos_new_campaign').value)
                        }
                    >
                        {__('Add campaign', 'kudos-donations')}
                    </Button>
                </BaseControl>

            </PanelBody>

            { props.settings._kudos_campaigns.map((form, i) => {

                return(
                    <CampaignPanel
                        key={ form.name }
                        allowDelete={ !form.protected }
                        settings={ props.settings }
                        campaign={ props.settings._kudos_campaigns[i] }
                        updateSetting={ props.updateSetting }
                        showNotice={ props.showNotice }
                        handleInputChange={ props.handleInputChange }
                    />
                )

            })}

        </div>
    );
};

export { AddCampaignPanel };
