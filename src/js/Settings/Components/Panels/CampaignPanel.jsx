import {Info} from "../Info"
import {SettingCard} from "../SettingCard"
import {ButtonIcon} from "../ButtonIcon"

const CampaignPanel = ({settings, campaign, removeCampaign, handleInputChange, allowDelete = false}) => {

    const {__} = wp.i18n
    const {useState, useEffect, Fragment} = wp.element
    const {
        Button,
        CardDivider,
        CardFooter,
        CheckboxControl,
        ClipboardButton,
        Disabled,
        RadioControl,
        TextControl,
        ToggleControl
    } = wp.components

    const [hasCopied, setHasCopied] = useState(false)
    let recurring_allowed = settings._kudos_vendor_mollie.recurring

    useEffect(() => {
        setHasCopied(false)
    }, [campaign])

    let donation_type = <RadioControl
        selected={!recurring_allowed ? 'oneoff' : campaign.donation_type || 'oneoff'}
        help={recurring_allowed ?
            __('The donation type of the form, set to "both" to allow donor to choose.', 'kudos-donations') :
            <Info
                level="warning">{__('You need to enable SEPA Direct Debit or credit card in your Mollie account to use subscription payments.', 'kudos-donations')}</Info>
        }
        options={[
            {label: __('One-off', 'kudos-donations'), value: 'oneoff'},
            {label: __('Subscription', 'kudos-donations'), value: 'recurring'},
            {label: __('Both', 'kudos-donations'), value: 'both'},
        ]}
        onChange={(value) => {
            campaign.donation_type = value
            handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
        }}
    />

    if (!recurring_allowed) {
        donation_type =
            <Disabled>
                {donation_type}
            </Disabled>
    }

    return (
        <div id={"campaign-" + campaign.id}>
            <SettingCard title={__('General', 'kudos-donations')} id="campaignPanel" settings={settings}
                         campaign={campaign} handleInputChange={handleInputChange}>
                <TextControl
                    label={__('Name', 'kudos-donations')}
                    help={__('Ensure that this is a unique name to make it easy to identify in the transactions page.', 'kudos-donations')}
                    type={'text'}
                    value={campaign.name || ''}
                    onChange={(value) => {
                        campaign.name = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

            </SettingCard>

            <CardDivider/>

            <SettingCard title={__('Goal', 'kudos-donations')}>
                <TextControl
                    label={__('Target amount', 'kudos-donations')}
                    help={__('Set a numeric goal for your campaign.', 'kudos-donations')}
                    type="number"
                    value={campaign.campaign_goal || ''}
                    onChange={(value) => {
                        campaign.campaign_goal = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

                <TextControl
                    label={__('Additional funds', 'kudos-donations')}
                    help={__('Add additional funds for your campaign to count towards the total. This will only be used to show progress to your donors.', 'kudos-donations')}
                    type="number"
                    value={campaign.additional_funds || ''}
                    onChange={(value) => {
                        campaign.additional_funds = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

                <ToggleControl
                    help={__('Show goal progression on donation form.', 'kudos-donations')}
                    label={campaign.show_progress ? __('Enabled', 'kudos-donations') : __('Disabled', 'kudos-donations')}
                    checked={campaign.show_progress || ''}
                    onChange={(value) => {
                        campaign.show_progress = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />
            </SettingCard>

            <CardDivider/>

            <SettingCard title={__('Text', 'kudos-donations')}>
                <TextControl
                    label={__('Header', 'kudos-donations')}
                    help={__('Shown at the top of the form.', 'kudos-donations')}
                    type={'text'}
                    value={campaign.modal_title || ''}
                    onChange={(value) => {
                        campaign.modal_title = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />
                <br/>
                <TextControl
                    label={__('Welcome text', 'kudos-donations')}
                    help={__('Shown just under the header.', 'kudos-donations')}
                    type={'text'}
                    value={campaign.welcome_text || ''}
                    onChange={(value) => {
                        campaign.welcome_text = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />
            </SettingCard>

            <CardDivider/>

            <SettingCard title={__('Address field', 'kudos-donations')}>

                <ToggleControl
                    help={__('Whether to show the address fields or not.', 'kudos-donations')}
                    label={campaign.address_enabled ? __('Enabled', 'kudos-donations') : __('Disabled', 'kudos-donations')}
                    checked={campaign.address_enabled || ''}
                    onChange={(value) => {
                        campaign.address_enabled = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

                {campaign.address_enabled ?
                    <Fragment>
                        <br/>
                        <CheckboxControl
                            help={__('Make the address required.', 'kudos-donations')}
                            label={__('Required', "kudos-donations")}
                            checked={campaign.address_required || ''}
                            onChange={(value) => {
                                campaign.address_required = value
                                handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                            }}
                        />
                    </Fragment>
                    : ''}

            </SettingCard>

            <CardDivider/>

            <SettingCard title={__('Message field')}>
                <ToggleControl
                    help={__('Allow donors to leave a message with their donation.', 'kudos-donations')}
                    label={campaign.message_enabled ? __('Enabled', 'kudos-donations') : __('Disabled', 'kudos-donations')}
                    checked={campaign.message_enabled || ''}
                    onChange={(value) => {
                        campaign.message_enabled = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />
            </SettingCard>

            <CardDivider/>

            <SettingCard title={__('Donation type', 'kudos-donations')}>
                {donation_type}
            </SettingCard>

            <CardDivider/>

            <SettingCard title={__('Amount type', 'kudos-donations')}>
                <RadioControl
                    help={__('Configure the amount type for this form. When set to "Fixed" or "Both" you will need to configure the amounts below.', 'kudos-donations')}
                    selected={campaign.amount_type || 'both'}
                    options={[
                        {label: __('Open', 'kudos-donations'), value: 'open'},
                        {label: __('Fixed', 'kudos-donations'), value: 'fixed'},
                        {label: __('Both', 'kudos-donations'), value: 'both'},
                    ]}
                    onChange={(value) => {
                        campaign.amount_type = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

                {campaign.amount_type !== 'open' ?
                    <Fragment>
                        <br/>
                        <TextControl
                            label={__('Amounts', 'kudos-donations') + ':'}
                            id={'fixed_amounts' + '-' + campaign.name}
                            value={campaign.fixed_amounts || ''}
                            onChange={(value) => {
                                let valuesArray = value.split(',')
                                if (valuesArray.length <= 4) {
                                    campaign.fixed_amounts = value.replace(/[^,0-9]/g, '')
                                }
                                handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                            }}
                        />
                        <Info>{__('Enter a comma separated list of values to use. Maximum of four numbers.', 'kudos-donations')}</Info>
                    </Fragment>

                    : ''}
            </SettingCard>


            <CardFooter>
                <ClipboardButton
                    isSecondary
                    icon={(<ButtonIcon icon="copy"/>)}
                    text={'[kudos campaign_id="' + campaign.id + '"]'}
                    onClick={() => setHasCopied(true)}
                    onCopy={() => setHasCopied(true)}
                    onFinishCopy={() => setHasCopied(false)}
                >
                    {hasCopied ? __('Copied!', 'kudos-donations') : __('Copy Shortcode', 'kudos-donations')}
                </ClipboardButton>

                {allowDelete ?
                    <Button
                        isLink
                        isSmall
                        onClick={
                            () => {
                                if (window.confirm(__('Are you sure you wish to delete this campaign?', 'kudos-donations'))) removeCampaign(campaign.id)
                            }
                        }
                    >
                        {__('Delete campaign:', 'kudos-donations') + " " + campaign.name}
                    </Button>
                    : ''}

            </CardFooter>
        </div>
    )
}

export {CampaignPanel}