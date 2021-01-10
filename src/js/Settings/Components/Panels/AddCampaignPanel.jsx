import { CampaignPanel } from "./CampaignPanel"

const { __, sprintf } = wp.i18n;
const { PanelBody, TextControl, Button } = wp.components;
const { useState } = wp.element;

const AddCampaignPanel = ( props ) => {

    const [ addFormValue, setAddFormValue ] = useState('');
    const [ buttonDisabled, setButtonDisabled ] = useState(true);
    let current = props.settings._kudos_campaigns;

    const isValid = ( value ) => {

        if( '' === value.trim() || current.find( x => x.name.toLowerCase() === value.toLowerCase().trim() ) ) {
            setButtonDisabled(true)
            return false;
        }

        return true

    }

    const updateValue = ( value ) => {

        setAddFormValue( value );
        setButtonDisabled(!isValid(value));

    }

    const addCampaign = ( name ) => {

        // Add new campaign with defaults to top of array using unshift
        current.push({
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

            { props.settings._kudos_campaigns.map((form, i) => {

                return(
                    <CampaignPanel
                        key={ form.slug }
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
