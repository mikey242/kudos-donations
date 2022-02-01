import { __ } from '@wordpress/i18n'
import React from 'react'
import { FormProvider, useForm } from 'react-hook-form'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/react/solid'
import Initial from './form/Initial'
import PaymentFrequency from './form/PaymentFrequency'
import Address from './form/Address'
import Button from './form/Button'

function FormRouter (props) {
  const {
    step, title, campaign, description, formData
  } = props
  const { handlePrev, handleNext } = props
  const methods = useForm()
  const onSubmit = (data) => {
    handleNext(data)
  }

  const getStep = () => {
    switch (step) {
      case 1:
        return (
                    <Initial
                        title={title}
                        description={description}
                        values={formData}
                    />
        )
      case 2:
        return (
                    <PaymentFrequency
                        {...methods}
                        title={__('Subscription')}
                        description={__('How often would you like to donate?')}
                        values={formData}
                    />
        )
      case 3:
        return (
                    <Address
                        campaign={campaign}
                        title={__('Address')}
                        description={__('Please fill in your address')}
                        values={formData}
                    />
        )
      case 4:
        return (
                    <p>Step 4</p>
        )
      default:
        return ('')
            // do nothing
    }
  }

  return (
        <FormProvider {...methods}>
            <form onSubmit={methods.handleSubmit(onSubmit)}>
                {getStep()}
                <div className="kudos-modal-buttons mt-8 flex justify-between relative">
                    {step > 1 &&
                        (
                            <Button
                                type="button"
                                ariaLabel={__('Prev')}
                                onClick={handlePrev}
                            >
                                <ChevronLeftIcon width="1.5em"/>
                                {' '}
                                <span>Prev</span>
                            </Button>
                        )}
                    <Button
                        type="submit"
                        ariaLabel={__('Next')}
                        className="ml-auto"
                    >
                        <span>Next</span>
                        {' '}
                        <ChevronRightIcon width="1.5em"/>
                    </Button>
                </div>
            </form>
        </FormProvider>
  )
}

export default FormRouter
