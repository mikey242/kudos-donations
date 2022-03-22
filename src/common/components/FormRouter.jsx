import { __ } from '@wordpress/i18n'
import React from 'react'
import { FormProvider, useForm } from 'react-hook-form'
import { ChevronLeftIcon, ChevronRightIcon, LockClosedIcon } from '@heroicons/react/solid'
import Initial from './tabs/Initial'
import PaymentFrequency from './tabs/PaymentFrequency'
import Address from './tabs/Address'
import Button from './controls/Button'
import Message from './tabs/Message'
import Summary from './tabs/Summary'

const FormRouter = (props) => {
  const {
    step, title, campaign, description
  } = props
  const { handlePrev, handleNext, submitForm } = props
  const methods = useForm()

  const onSubmit = (data) => {
    console.log(data)
    if (step < 5) return handleNext(data, step + 1)
    return submitForm(data)
  }

  const CurrentStep = ({ buttons }) => {
    switch (step) {
      case 1:
        return (
                    <Initial
                        title={title}
                        description={description}
                        donationType={campaign.donation_type}
                        amountType={campaign.amount_type}
                        fixedAmounts={campaign.fixed_amounts}
                        buttons={buttons}
                    />
        )
      case 2:
        return (
                    <PaymentFrequency
                        title={__('Subscription')}
                        description={__('How often would you like to donate?')}
                        buttons={buttons}
                    />
        )
      case 3:
        return (
                    <Address
                        campaign={campaign}
                        title={__('Address')}
                        description={__('Please fill in your address')}
                        buttons={buttons}
                    />
        )
      case 4:
        return (
                    <Message
                        title={__('Message')}
                        description={__('Leave a message (optional).')}
                        buttons={buttons}
                    />
        )
      case 5:
        return (
                    <Summary
                        title={__('Payment')}
                        description={__('By clicking donate you agree to the following payment:')}
                        buttons={buttons}
                    />
        )
      default:
        return ('')
            // do nothing
    }
  }

  return (
        <FormProvider {...methods}>
            <form onSubmit={methods.handleSubmit(onSubmit)}>
                <CurrentStep/>
                <div className="kudos-modal-buttons mt-8 flex justify-between relative">
                    {step > 1 &&
                        (
                            <Button
                                type="button"
                                ariaLabel={__('Prev')}
                                onClick={handlePrev}
                            >
                                <ChevronLeftIcon className="w-5 h-5"/>
                                <span className="mx-2">Prev</span>
                            </Button>
                        )}
                    <Button
                        type="submit"
                        ariaLabel={__('Next')}
                        className="ml-auto"
                    >
                        {step < 5
                          ? <><span className="mx-2">{__('Next')}</span><ChevronRightIcon
                                className="w-5 h-5"/></>
                          : <><LockClosedIcon className="w-5 h-5"/> <span
                                className="mx-2">{__('Submit')}</span></>}
                    </Button>
                </div>
            </form>
        </FormProvider>
  )
}

export default FormRouter
