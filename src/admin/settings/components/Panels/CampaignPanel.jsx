import { Info } from '../Info'
import { SettingCard } from '../SettingCard'
import React from 'react'
import { __ } from '@wordpress/i18n'
import { useState, Fragment } from '@wordpress/element'
import {
  Button,
  CardDivider,
  CardFooter,
  CheckboxControl,
  Disabled,
  RadioControl,
  TextControl,
  ToggleControl
} from '@wordpress/components'
import { CopyToClipBoard } from '../CopyToClipBoard'
import apiFetch from '@wordpress/api-fetch'

const CampaignPanel = ({
  campaign,
  removeCampaign,
  handleInputChange,
  isRecurringAllowed,
  settings,
  allowDelete = false
}) => {
  const [hasCopied, setHasCopied] = useState(false)

  const deleteCampaign = (id) => {
    apiFetch({
      path: `wp/v2/kudos_campaign/${id}`,
      method: 'DELETE'
    }).then((response) => {
      console.log(response)
    })
  }

  const donationType = <RadioControl
        selected={!isRecurringAllowed ? 'oneoff' : campaign.meta.donation_type || 'oneoff'}
        help={isRecurringAllowed
          ? __('The donation type of the tabs, set to "both" to allow donor to choose.', 'kudos-donations')
          : <Info
                level="warning">{__('You need to enable SEPA Direct Debit or credit card in your Mollie account to use subscription payments.', 'kudos-donations')}</Info>
        }
        options={[
          { label: __('One-off', 'kudos-donations'), value: 'oneoff' },
          { label: __('Subscription', 'kudos-donations'), value: 'recurring' },
          { label: __('Both', 'kudos-donations'), value: 'both' }
        ]}
        onChange={(value) => {
          campaign.meta.donation_type = value
          handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
        }}
    />

  if (!isRecurringAllowed) {
    donationType =
            <Disabled>
                {donationType}
            </Disabled>
  }

  return (
        <div id={'campaign-' + campaign.slug}>
            <SettingCard title={__('General', 'kudos-donations')} id="campaignPanel"
                         campaign={campaign} handleInputChange={handleInputChange}>
                <TextControl
                    label={__('Name', 'kudos-donations')}
                    help={__('Ensure that this is a unique name to make it easy to identify in the transactions page.', 'kudos-donations')}
                    type={'text'}
                    value={campaign.title.rendered || ''}
                    onChange={(value) => {
                      campaign.title.rendered = value
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
                    value={campaign.meta.campaign_goal || ''}
                    onChange={(value) => {
                      campaign.meta.campaign_goal = value
                      handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

                <TextControl
                    label={__('Additional funds', 'kudos-donations')}
                    help={__('Add additional funds for your campaign to count towards the total. This will only be used to show progress to your donors.', 'kudos-donations')}
                    type="number"
                    value={campaign.meta.additional_funds || ''}
                    onChange={(value) => {
                      campaign.meta.additional_funds = value
                      handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

                <ToggleControl
                    help={__('Show goal progression on donation tabs.', 'kudos-donations')}
                    label={campaign.meta.show_progress ? __('Enabled', 'kudos-donations') : __('Disabled', 'kudos-donations')}
                    checked={campaign.meta.show_progress || ''}
                    onChange={(value) => {
                      campaign.meta.show_progress = value
                      handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />
            </SettingCard>

            <CardDivider/>

            <SettingCard title={__('Text', 'kudos-donations')}>
                <TextControl
                    label={__('Header', 'kudos-donations')}
                    help={__('Shown at the top of the tabs.', 'kudos-donations')}
                    type={'text'}
                    value={campaign.meta.modal_title || ''}
                    onChange={(value) => {
                      campaign.meta.modal_title = value
                      handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />
                <br/>
                <TextControl
                    label={__('Welcome text', 'kudos-donations')}
                    help={__('Shown just under the header.', 'kudos-donations')}
                    type={'text'}
                    value={campaign.meta.welcome_text || ''}
                    onChange={(value) => {
                      campaign.meta.welcome_text = value
                      handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />
            </SettingCard>

            <CardDivider/>

            <SettingCard title={__('Address field', 'kudos-donations')}>

                <ToggleControl
                    help={__('Whether to show the address fields or not.', 'kudos-donations')}
                    label={campaign.meta.address_enabled ? __('Enabled', 'kudos-donations') : __('Disabled', 'kudos-donations')}
                    checked={campaign.meta.address_enabled || ''}
                    onChange={(value) => {
                      campaign.meta.address_enabled = value
                      handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

                {campaign.meta.address_enabled
                  ? <Fragment>
                        <br/>
                        <CheckboxControl
                            help={__('Make the address required.', 'kudos-donations')}
                            label={__('Required', 'kudos-donations')}
                            checked={campaign.meta.address_required || ''}
                            onChange={(value) => {
                              campaign.meta.address_required = value
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
                    label={campaign.meta.message_enabled ? __('Enabled', 'kudos-donations') : __('Disabled', 'kudos-donations')}
                    checked={campaign.meta.message_enabled || ''}
                    onChange={(value) => {
                      campaign.meta.message_enabled = value
                      handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />
            </SettingCard>

            <CardDivider/>

            <SettingCard title={__('Donation type', 'kudos-donations')}>
                {donationType}
            </SettingCard>

            <CardDivider/>

            <SettingCard title={__('Amount type', 'kudos-donations')}>
                <RadioControl
                    help={__('Configure the amount type for this tabs. When set to "Fixed" or "Both" you will need to configure the amounts below.', 'kudos-donations')}
                    selected={campaign.meta.amount_type || 'both'}
                    options={[
                      { label: __('Open', 'kudos-donations'), value: 'open' },
                      { label: __('Fixed', 'kudos-donations'), value: 'fixed' },
                      { label: __('Both', 'kudos-donations'), value: 'both' }
                    ]}
                    onChange={(value) => {
                      campaign.meta.amount_type = value
                      handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                    }}
                />

                {campaign.meta.amount_type !== 'open'
                  ? <Fragment>
                        <br/>
                        <TextControl
                            label={__('Amounts', 'kudos-donations') + ':'}
                            id={'fixed_amounts' + '-' + campaign.title.rendered}
                            value={campaign.meta.fixed_amounts || ''}
                            onChange={(value) => {
                              const valuesArray = value.split(',')
                              if (valuesArray.length <= 4) {
                                campaign.meta.fixed_amounts = value.replace(/[^,0-9]/g, '')
                              }
                              handleInputChange('_kudos_campaigns', settings._kudos_campaigns)
                            }}
                        />
                        <Info>{__('Enter a comma separated list of values to use. Maximum of four numbers.', 'kudos-donations')}</Info>
                    </Fragment>

                  : ''}
            </SettingCard>

            <CardFooter className={'box-border'}>
                <CopyToClipBoard
                    text={'[kudos campaign_id="' + campaign.id + '"]'}
                    onCopy={() => setHasCopied(true)}
                    onFinishCopy={() => setHasCopied(false)}
                >
                    {hasCopied ? __('Copied!', 'kudos-donations') : __('Copy Shortcode', 'kudos-donations')}
                </CopyToClipBoard>
                {allowDelete
                  ? <Button
                        isLink
                        isSmall
                        onClick={
                            () => {
                              if (window.confirm(__('Are you sure you wish to delete this campaign?', 'kudos-donations'))) removeCampaign(campaign.id)
                            }
                        }
                    >
                        {__('Delete campaign:', 'kudos-donations') + ' ' + campaign.title.rendered}
                    </Button>
                  : ''}

            </CardFooter>
        </div>
  )
}

export { CampaignPanel }