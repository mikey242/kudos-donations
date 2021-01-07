const { __, sprintf } = wp.i18n;
const { CheckboxControl, PanelBody, BaseControl, RadioControl, ToggleControl, TextControl, Button } = wp.components;
const { useState } = wp.element;

const CampaignsPanel = (props ) => {

    const [ addFormValue, setAddFormValue ] = useState('');

    const saveCampaigns = () => {
        props.handleInputChange('_kudos_campaigns', props.settings._kudos_campaigns)
    }

    const addCampaign = ( name ) => {

        let current = props.settings._kudos_campaigns;

        // Bail if name is empty
        if( '' === name ) {
            props.showNotice(__('Campaign name empty.', 'kudos-donations'))
            return;
        }

        // Bail if duplicate found
        if(current.find( x => x.name.toLowerCase() === name.toLowerCase() )) {
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

    const removeCampaign = ( slug ) => {
        let current = props.settings._kudos_campaigns;
        let updated = current.filter( function (value) {
            return value.slug !== slug
        } )
        props.updateSetting('_kudos_campaigns', _.uniq(updated, 'slug') );
        props.showNotice( __('Campaign deleted.', 'kudos-donations') )
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
                        isSecondary
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

                return (
                    <PanelBody
                        title={ __('Campaign', 'kudos-donations') + ' - ' + form.name }
                        key={ form.name }
                        initialOpen={ false }
                    >

                        <BaseControl
                            id={ form.slug + "-modal-text" }
                            help={'Customize the text of the form'}
                        >

                            <TextControl
                                label={ __( 'Header', 'kudos-donations' ) }
                                id={ 'modal_title' + '-' + form.name }
                                className={ 'kd-inline' }
                                type={ 'text' }
                                value={ form.modal_title || '' }
                                onChange={ (value) => {
                                    props.settings._kudos_campaigns[i].modal_title = value
                                    saveCampaigns()
                                } }
                            />

                            <TextControl
                                label={ __( 'Welcome text', 'kudos-donations' ) }
                                id={ 'welcome_text' + '-' + form.name }
                                className={ 'kd-inline' }
                                type={ 'text' }
                                value={ form.welcome_text || '' }
                                onChange={ (value) => {
                                    props.settings._kudos_campaigns[i].welcome_text = value
                                    saveCampaigns()
                                } }
                            />

                        </BaseControl>

                        <BaseControl
                            id={ form.slug + "-address" }
                            label="Address"
                            help={'Configure the address fields'}
                        >

                        <ToggleControl
                            label={__("Enabled", 'kudos-donations')}
                            checked={ form.address_enabled || '' }
                            onChange={ (value) => {
                                props.settings._kudos_campaigns[i].address_enabled = value
                                saveCampaigns()
                            } }
                        />

                        { props.settings._kudos_campaigns[i].address_enabled ?

                            <CheckboxControl
                                label={__("Required", "kudos-donations")}
                                checked={form.address_required || ''}
                                onChange={ (value) => {
                                    props.settings._kudos_campaigns[i].address_required = value
                                    saveCampaigns()
                                }}
                            />

                        : ''}

                        </BaseControl>

                        <BaseControl
                            id={ form.slug + "-donation-type" }
                            label="Donation type"
                            help={'Set the donation type available'}
                        >

                        <RadioControl
                            selected={ form.donation_type || 'both' }
                            options={ [
                                { label: __('One-off', 'kudos-donations'), value: 'oneoff' },
                                { label: __('Subscription', 'kudos-donations'), value: 'recurring' },
                                { label: __('Both', 'kudos-donations'), value: 'both' },
                            ] }
                            onChange={ (value) => {
                                props.settings._kudos_campaigns[i].donation_type = value
                                saveCampaigns()
                            }}
                        />

                        </BaseControl>

                        <RadioControl
                            label={ __( 'Amount type', 'kudos-donations' ) }
                            selected={ form.amount_type || 'both' }
                            options={ [
                                { label: __('Open', 'kudos-donations'), value: 'open' },
                                { label: __('Fixed', 'kudos-donations'), value: 'fixed' },
                                { label: __('Both', 'kudos-donations'), value: 'both' },
                            ] }
                            onChange={ (value) => {
                                props.settings._kudos_campaigns[i].amount_type = value
                                saveCampaigns()
                            }}
                        />

                        { form.amount_type !== 'open' ?

                            <TextControl
                                label={ __(	'Amounts',	'kudos-donations' ) + ':' }
                                id={ 'fixed_amounts' + '-' + form.name }
                                help={ __( 'Enter a comma separated list of values to use. Maximum of four numbers.', 'kudos-donations' ) }
                                value={ form.fixed_amounts || '' }
                                onChange={ (value) => {
                                    let valuesArray = value.split(',');
                                    if(valuesArray.length <= 4) {
                                        props.settings._kudos_campaigns[i].fixed_amounts = value.replace(/[^,0-9]/g, '')
                                    }
                                    saveCampaigns()
                                }}
                            />

                        : '' }

                        <Button
                            isSecondary
                            isSmall
                            onClick={
                                () => removeCampaign( form.slug )
                            }
                        >
                            { __('Delete', 'kudos-donations') + ' ' + form.name }
                        </Button>

                    </PanelBody>
                )
            })}
        </div>
    );
};

export { CampaignsPanel };
