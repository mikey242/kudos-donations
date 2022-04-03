import React from 'react'
import { Fragment, useEffect } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { useForm, FormProvider } from 'react-hook-form'
import TextControl from '../../common/components/controls/TextControl'
import ToggleControl from '../../common/components/controls/ToggleControl'
import Button from '../../common/components/controls/Button'
import TextAreaControl from '../../common/components/controls/TextAreaControl'
import RadioGroupControl from '../../common/components/controls/RadioGroupControl'
import ColorPicker from '../../common/components/controls/ColorPicker'
import TabPanel from './TabPanel'

function CampaignEdit ({ campaign, updateCampaign, setCurrentCampaign, recurringAllowed }) {
  const methods = useForm({
    defaultValues: {
      ...campaign,
      title: campaign?.title?.rendered
    }
  })
  const { reset, handleSubmit, watch } = methods

  const watchAmountType = watch('meta.amount_type')

  useEffect(() => {
    reset({
      ...campaign,
      title: campaign?.title?.rendered
    })
  }, [campaign])

  const onSubmit = (data) => {
    updateCampaign(data.id, data)
  }

  const tabs = [
    {
      name: 'general',
      title: __('General', 'kudos-donations'),
      content:
                <Fragment>
                    <TextControl name="title" label={__('Campaign name', 'kudos-donations')}
                                 validation={{ required: __('Name required') }}/>
                    <TextControl type="number" name="meta.goal" label="Goal"/>
                    <ColorPicker label={__('Theme color', 'kudos-donations')} name="meta.theme_color"/>
                </Fragment>
    },
    {
      name: 'text-fields',
      title: __('Text fields', 'kudos-donations'),
      content:
                <Fragment>
                    <TextControl name="meta.initial_title" label="Welcome Title"/>
                    <TextAreaControl name="meta.initial_text" label="Welcome Text"
                                     placeholder="Welcome Text"/>
                </Fragment>
    },
    {
      name: 'donation-settings',
      title: __('Donation settings', 'kudos-donations'),
      content:
                <Fragment>
                    <RadioGroupControl
                        name="meta.donation_type"
                        label={__('Donation type', 'kudos-donations')}
                        options={[
                          { label: __('One-off', 'kudos-donations'), value: 'oneoff' },
                          {
                            label: __('Subscription', 'kudos-donations'),
                            value: 'recurring',
                            disabled: !recurringAllowed
                          },
                          {
                            label: __('Both', 'kudos-donations'),
                            value: 'both',
                            disabled: !recurringAllowed
                          }
                        ]}/>
                    <RadioGroupControl
                        name="meta.amount_type" label={__('Payment type', 'kudos-donations')}
                        options={[
                          { label: __('Open', 'kudos-donations'), value: 'open' },
                          { label: __('Fixed', 'kudos-donations'), value: 'fixed' },
                          { label: __('Both', 'kudos-donations'), value: 'both' }

                        ]}/>
                    {watchAmountType !== 'open' &&
                        <TextControl name="meta.fixed_amounts" label={__('Fixed amounts', 'kudos-donations')}/>
                    }
                </Fragment>
    },
    {
      name: 'optional-fields',
      title: __('Optional fields', 'kudos-donations'),
      content:
                <Fragment>
                    <ToggleControl name="meta.address_enabled" label={__('Address')}/>
                    <ToggleControl name="meta.message_enabled" label={__('Message')}/>
                    <TextControl name="meta.terms_link" label={__('Terms and Conditions URL', 'kudos-donations')}/>
                    <TextControl name="meta.privacy_link" label={__('Privacy Policy URL', 'kudos-donations')}/>
                </Fragment>
    }
  ]

  return (
        <Fragment>
            <h2 className="text-center my-5">{campaign.status === 'draft' ? __('New campaign', 'kudos-donations') : __('Edit campaign', 'kudos-donations')}</h2>
            <FormProvider {...methods}>
                <form id="settings-form" onSubmit={handleSubmit(onSubmit)}>
                    <TabPanel
                        tabs={tabs}
                    />
                </form>
                <div className="text-right flex justify-between mt-5">
                    <Button onClick={() => setCurrentCampaign(null)}
                            type="button">{__('Cancel', 'kudos-donations')}</Button>
                    <Button form="settings-form" type="submit">
                        {campaign.status === 'draft' ? __('Create', 'kudos-donations') : __('Save', 'kudos-donations')}
                    </Button>
                </div>
            </FormProvider>

        </Fragment>
  )
}

export default CampaignEdit
