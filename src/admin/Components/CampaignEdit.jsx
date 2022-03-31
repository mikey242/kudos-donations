import React from 'react'
import { Fragment, useEffect } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { useForm, FormProvider } from 'react-hook-form'
import InputControl from '../../common/components/controls/InputControl'
import ToggleControl from '../../common/components/controls/ToggleControl'
import Button from '../../common/components/controls/Button'
import TextAreaControl from '../../common/components/controls/TextAreaControl'
import RadioGroupControl from '../../common/components/controls/RadioGroupControl'
import Panel from './Panel'
import ColorPicker from '../../common/components/controls/ColorPicker'

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

  return (
        <Fragment>
            <h2 className="text-center my-5">{campaign.status === 'draft' ? __('New campaign', 'kudos-donations') : __('Edit campaign', 'kudos-donations')}</h2>
            <Panel>
                <div className="p-5">

                    <FormProvider {...methods}>
                        <form onSubmit={handleSubmit(onSubmit)}>
                            {/* <h3>Campaign details</h2> */}
                            <InputControl name="title" label={__('Campaign name', 'kudos-donations')}
                                          validation={{ required: __('Name required') }}/>
                            <InputControl type="number" name="meta.goal" label="Goal"/>
                            <ColorPicker label={__('Theme color', 'kudos-donations')} name="meta.theme_color"/>
                            <hr className="my-5 border-gray-50"/>
                            <h3>{__('Text fields', 'kudos-donations')}</h3>
                            <InputControl name="meta.initial_title" label="Welcome Title"/>
                            <TextAreaControl name="meta.initial_text" label="Welcome Text"
                                             placeholder="Welcome Text"/>
                            <hr className="my-5 border-gray-50"/>
                            <h3>{__('Donation settings', 'kudos-donations')}</h3>
                            <RadioGroupControl name="meta.donation_type" label={__('Donation type', 'kudos-donations')}
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
                            <RadioGroupControl name="meta.amount_type" label={__('Payment type', 'kudos-donations')}
                                               options={[
                                                 { label: __('Open', 'kudos-donations'), value: 'open' },
                                                 { label: __('Fixed', 'kudos-donations'), value: 'fixed' },
                                                 { label: __('Both', 'kudos-donations'), value: 'both' }

                                               ]}/>
                            {watchAmountType !== 'open' &&
                                <InputControl name="meta.fixed_amounts" label={__('Fixed amounts', 'kudos-donations')}/>
                            }
                            <hr className="my-5 border-gray-50"/>
                            <h3>{__('Optional fields', 'kudos-donations')}</h3>
                            <ToggleControl name="meta.address_enabled" label={__('Address')}/>
                            <ToggleControl name="meta.message_enabled" label={__('Message')}/>
                            <hr className="my-5 border-gray-50"/>
                            <div className="text-right flex justify-between mt-5">
                                <Button onClick={() => setCurrentCampaign(null)}
                                        type="button">{__('Cancel', 'kudos-donations')}</Button>
                                <Button type="submit">
                                    {campaign.status === 'draft' ? __('Create', 'kudos-donations') : __('Save', 'kudos-donations')}
                                </Button>
                            </div>

                        </form>
                    </FormProvider>
                </div>
            </Panel>
        </Fragment>
  )
}

export default CampaignEdit
