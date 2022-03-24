import React from 'react'
import Panel from './Panel'
import { useForm, FormProvider } from 'react-hook-form'
import InputControl from '../../common/components/controls/InputControl'
import ToggleControl from '../../common/components/controls/ToggleControl'
import { __ } from '@wordpress/i18n'
import Button from '../Components/Controls/Button'
import apiFetch from '@wordpress/api-fetch'

function CampaignEdit ({ campaign, updateCampaign }) {
  const methods = useForm({ defaultValues: { ...campaign, title: campaign.title.rendered } })

  const onSubmit = (data) => {
    console.log(data)
    updateCampaign(data.id, data)
  }

  return (
        <Panel>
            <div className="p-5">
                <FormProvider {...methods}>
                    <form onSubmit={methods.handleSubmit(onSubmit)}>
                        <h2>Optional fields</h2>
                        <InputControl name="title" label="Campaign name"
                                      validation={{ required: __('Name required') }}/>
                        <InputControl type="number" name="meta.goal" label="Goal"/>
                        <hr className="my-5"/>
                        <h2>Optional fields</h2>
                        <ToggleControl name="meta.address_enabled" label={__('Address')}/>
                        <ToggleControl name="meta.message_enabled" label={__('Message')}/>
                        <div className="text-right">
                            <Button type="submit">Submit</Button>
                        </div>
                    </form>
                </FormProvider>
            </div>
        </Panel>

  )
}

export default CampaignEdit
