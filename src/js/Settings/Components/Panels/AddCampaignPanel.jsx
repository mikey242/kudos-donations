import {CampaignPanel} from "./CampaignPanel"

const { __, sprintf } = wp.i18n;
const { PanelBody, TextControl, Button } = wp.components;
const { useState } = wp.element;

const AddCampaignPanel = ({settings, showNotice, updateSetting, handleInputChange} ) => {

    const [ addFormValue, setAddFormValue ] = useState('');
    const [ buttonDisabled, setButtonDisabled ] = useState(true);
    let current = settings._kudos_campaigns;

    const isCampaignNameValid = ( name ) => {
        return !('' === name.trim() || settings._kudos_campaigns.find(x => x.name.toLowerCase() === name.toLowerCase().trim()));
    }

    const updateValue = ( value ) => {
        setAddFormValue( value );
        setButtonDisabled(!isCampaignNameValid(value));
    }

    const addCampaign = ( name ) => {

        // Add new campaign with defaults to top of array using unshift
        current.push({
            name: name,
            modal_title: __( 'Support us!', 'kudos-donations' ),
            welcome_text: __( 'Your support is greatly appreciated and will help to keep us going.', 'kudos-donations' ),
            donation_type: 'both',
            amount_type: 'both',
            fixed_amounts: '5,10,20,50'
        })

        // Save changes and show notice
        updateSetting('_kudos_campaigns');
        setButtonDisabled(true);
        showNotice( sprintf(__('Added campaign "%s".', 'kudos-donations'), name) )
    };

    return (
        <div>
            <PanelBody
                title={ __( 'Add a campaign', 'kudos-donations' ) }
                opened={ true }
            >

                <TextControl
                    label={ __(
                        'Campaign name',
                        'kudos-donations'
                    ) }
                    help={__("Give your campaign a unique name to identify it.", 'kudos-donations')}
                    id={'kudos_new_campaign'}
                    className={'kd-inline'}
                    type={ 'text' }
                    value={ addFormValue }
                    onChange={ (newValue) => updateValue( newValue ) }
                />

                <br/>

                <Button
                    isPrimary
                    isSmall
                    disabled={ buttonDisabled }
                    onClick={
                        () => addCampaign(document.getElementById('kudos_new_campaign').value)
                    }
                >
                    {__('Add campaign', 'kudos-donations')}
                </Button>

            </PanelBody>

            { settings._kudos_campaigns.map((campaign, i) => {

                return(
                    <CampaignPanel
                        key={ 'campaign_' + i }
                        allowDelete={ !campaign.protected }
                        settings={ settings }
                        campaign={ settings._kudos_campaigns[i] }
                        isCampaignNameValid={ isCampaignNameValid }
                        updateSetting={ updateSetting }
                        showNotice={ showNotice }
                        handleInputChange={ handleInputChange }
                    />
                )

            })}
        </div>
    );
};

export { AddCampaignPanel };
