import {Info} from "../Info"

const {__} = wp.i18n
const {useState} = wp.element
const {
    BaseControl,
    Button,
    CheckboxControl,
    ClipboardButton,
    PanelBody,
    RadioControl,
    TextControl,
    ToggleControl
} = wp.components

const CampaignPanel = ({ settings, campaign, updateSetting, handleInputChange, isOpen=false, allowDelete = false}) => {

    const [hasCopied, setHasCopied] = useState(false)

    const removeCampaign = (id) => {
        let current = settings._kudos_campaigns
        let updated = current.filter(function (value) {
            return value.id !== id
        })
        updateSetting('_kudos_campaigns', _.uniq(updated, 'id'), __('Campaign deleted.', 'kudos-donations'))
    }

    return (
        <PanelBody
            title={campaign.name}
            initialOpen={false}
        >

            <BaseControl>

                <TextControl
                    label={__('Name', 'kudos-donations')}
                    id={'campaign_name' + '-' + campaign.id}
                    type={'text'}
                    value={campaign.name || ''}
                    onChange={(value) => {
                        campaign.name = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

                <TextControl
                    label={__('Header', 'kudos-donations')}
                    id={'modal_title' + '-' + campaign.id}
                    type={'text'}
                    value={campaign.modal_title || ''}
                    onChange={(value) => {
                        campaign.modal_title = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

                <TextControl
                    label={__('Welcome text', 'kudos-donations')}
                    id={'welcome_text' + '-' + campaign.id}
                    type={'text'}
                    value={campaign.welcome_text || ''}
                    onChange={(value) => {
                        campaign.welcome_text = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

            </BaseControl>

            <BaseControl
                label="Address"
            >


                <ToggleControl
                    label={__("Enabled", 'kudos-donations')}
                    checked={campaign.address_enabled || ''}
                    onChange={(value) => {
                        campaign.address_enabled = value
                        handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

                {campaign.address_enabled ?

                    <CheckboxControl
                        label={__("Required", "kudos-donations")}
                        checked={campaign.address_required || ''}
                        onChange={(value) => {
                            campaign.address_required = value
                            handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                        }}
                    />

                    : ''}


            </BaseControl>

            <BaseControl
                label={__("Donation type", 'kudos-donations')}

            >

                <RadioControl
                    selected={campaign.donation_type || 'both'}
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

            </BaseControl>

            <BaseControl>

                <RadioControl
                    label={__('Amount type', 'kudos-donations')}
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

            </BaseControl>

                {campaign.amount_type !== 'open' ?

                    <BaseControl>
                        <TextControl
                            label={__('Amounts', 'kudos-donations') + ':'}
                            id={'fixed_amounts' + '-' + campaign.name}
                            // help={__('Enter a comma separated list of values to use. Maximum of four numbers.', 'kudos-donations')}
                            value={campaign.fixed_amounts || ''}
                            onChange={(value) => {
                                let valuesArray = value.split(',')
                                if (valuesArray.length <= 4) {
                                    campaign.fixed_amounts = value.replace(/[^,0-9]/g, '')
                                }
                                handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                            }}
                        />
                        <Info>
                            {__('Enter a comma separated list of values to use. Maximum of four numbers.', 'kudos-donations')}
                        </Info>
                    </BaseControl>

                    : ''}


            <div className="kd-flex kd-justify-between">

                <div>
                    <ClipboardButton
                        isSecondary
                        text={'[kudos campaign_id="' + campaign.id + '"]'}
                        onCopy={() => setHasCopied(true)}
                        onFinishCopy={() => setHasCopied(false)}
                    >
                        {hasCopied ? 'Copied!' : 'Copy Shortcode'}
                    </ClipboardButton>
                </div>

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
                        {__('Delete campaign', 'kudos-donations')}
                    </Button>

                    : ''}

            </div>

        </PanelBody>
    )
}

export {CampaignPanel}