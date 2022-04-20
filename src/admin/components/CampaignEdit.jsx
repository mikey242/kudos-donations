import React from 'react'
import { Fragment, useEffect } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { useForm, FormProvider } from 'react-hook-form'
import {
  TextControl,
  ToggleControl,
  TextAreaControl,
  ColorPicker,
  RadioGroupControl,
  Button
} from '../../common/components/controls'
import TabPanel from './TabPanel'
import Divider from './Divider'
import { ArrowCircleLeftIcon } from '@heroicons/react/outline'
import { isValidUrl } from '../../common/helpers/util'

function CampaignEdit ({ campaign, updateCampaign, setCurrentCampaign, recurringAllowed }) {
  const methods = useForm()
  const { reset, handleSubmit, watch, formState } = methods

  const watchAmountType = watch('meta.amount_type')
  const watchUseReturnURL = watch('meta.use_custom_return_url')

  useEffect(() => {
    reset({
      ...campaign,
      title: campaign?.title?.rendered
    })
  }, [campaign])

  const goBack = () => {
    if (Object.keys(formState.dirtyFields).length) {
      window.confirm(__('You have unsaved changes, are you sure you want to leave?')) && setCurrentCampaign(null)
    } else {
      setCurrentCampaign(null)
    }
  }

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
                                 help={__('Give your campaign a unique name', 'kudos-donations')}
                                 validation={{ required: __('Name required') }}/>
                    <br/>
                    <TextControl type="number" name="meta.goal"
                                 addOn="€"
                                 help={__('Set a goal for your campaign', 'kudos-donations')}
                                 label={__('Goal', 'kudos-donations')}/>
                    <br/>
                    <ColorPicker name="meta.theme_color" label={__('Theme color', 'kudos-donations')}
                                 help={__('Choose a color them for your campaign', 'kudos-donations')}/>
                    <br/>
                    <p className="block text-sm font-medium font-bold text-gray-700">Completed payment</p>
                    <ToggleControl
                        name="meta.show_return_message"
                        label={__('Show return message', 'kudos-donations')}
                    />
                    <ToggleControl
                        name="meta.use_custom_return_url"
                        label={__('Use custom return URL', 'kudos-donations')}
                    />
                    {watchUseReturnURL &&
                        <TextControl name="meta.custom_return_url"
                                     validation={{
                                       required: __('Name required'),
                                       validate: value => isValidUrl(value)
                                     }}/>
                    }
                </Fragment>
    },
    {
      name: 'text-fields',
      title: __('Text fields', 'kudos-donations'),
      content:
                <Fragment>
                    <h3>{__('Initial tab', 'kudos-donations')}</h3>
                    <TextControl
                        name="meta.initial_title"
                        label={__('Title', 'kudos-donations')}
                    />
                    <TextAreaControl
                        name="meta.initial_text"
                        label={__('Text', 'kudos-donations')}
                    />
                    <Divider/>
                    <h3>{__('Completed payment', 'kudos-donations')}</h3>
                    <TextControl
                        name="meta.return_message_title"
                        label={__('Message title', 'kudos-donations')}
                    />
                    <TextAreaControl
                        name="meta.return_message_text"
                        label={__('Message title', 'kudos-donations')}
                    />
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
                        help={__('Chose the available payment frequency', 'kudos-donations')}
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
                        help={__('Chose the available amount types', 'kudos-donations')}
                        options={[
                          { label: __('Open', 'kudos-donations'), value: 'open' },
                          { label: __('Fixed', 'kudos-donations'), value: 'fixed' },
                          { label: __('Both', 'kudos-donations'), value: 'both' }

                        ]}/>
                    {watchAmountType !== 'open' &&
                        <TextControl
                            name="meta.fixed_amounts"
                            help={__('Comma-separated list of amounts', 'kudos-donations')}
                            label={__('Fixed amounts', 'kudos-donations')}/>
                    }
                </Fragment>
    },
    {
      name: 'optional-fields',
      title: __('Optional fields', 'kudos-donations'),
      content:
                <Fragment>
                    <ToggleControl name="meta.address_enabled" label={__('Address')}
                                   help={__('Show the address tab', 'kudos-donations')}/>
                    <ToggleControl name="meta.message_enabled" label={__('Message')}
                                   help={__('Allow donors to leave a message', 'kudos-donations')}/>
                    <TextControl name="meta.terms_link" label={__('Terms and Conditions URL', 'kudos-donations')}/>
                    <TextControl name="meta.privacy_link" label={__('Privacy Policy URL', 'kudos-donations')}/>
                </Fragment>
    }
  ]

  return (
        <Fragment>
            <h2 className="text-center my-5">{campaign.status === 'draft' ? __('New campaign', 'kudos-donations') : __('Edit campaign: ', 'kudos-donations') + campaign.title.rendered}</h2>
            <FormProvider {...methods}>
                <form id="settings-form" onSubmit={handleSubmit(onSubmit)}>
                    <TabPanel
                        tabs={tabs}
                    />
                </form>
                <div className="text-right flex justify-between mt-5">
                    <Button isLink onClick={() => goBack()}
                            type="button">
                        <ArrowCircleLeftIcon className="mr-2 w-5 h-5"/>{__('Back', 'kudos-donations')}
                    </Button>
                </div>
            </FormProvider>

        </Fragment>
  )
}

export default CampaignEdit
