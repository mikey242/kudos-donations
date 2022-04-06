import { __ } from '@wordpress/i18n'
import React from 'react'
import { FormProvider, useForm } from 'react-hook-form'
import { ChevronLeftIcon, ChevronRightIcon, LockClosedIcon } from '@heroicons/react/solid'
import Initial from './tabs/Initial'
import PaymentFrequency from './tabs/PaymentFrequency'
import Address from './tabs/Address'
import { Button } from '../../common/components/controls'
import Message from './tabs/Message'
import Summary from './tabs/Summary'
import { steps } from '../constants/form'

const FormRouter = (props) => {
  const { step, campaign } = props
  const { handlePrev, handleNext, submitForm } = props
  const methods = useForm()

  const onSubmit = (data) => {
    if (step < 5) return handleNext(data, step + 1)
    return submitForm(data)
  }

  const CurrentStep = () => {
    switch (step) {
      case 1:
        return (
                    <Initial
                        title={campaign.initial_title}
                        description={campaign.initial_text}
                        donationType={campaign.donation_type}
                        amountType={campaign.amount_type}
                        fixedAmounts={campaign.fixed_amounts}
                    />
        )
      case 2:
        return (
                    <PaymentFrequency
                        title={__('Subscription')}
                        description={__('How often would you like to donate?')}
                    />
        )
      case 3:
        return (
                    <Address
                        required={campaign.address_required}
                        title={__('Address')}
                        description={__('Please fill in your address')}
                    />
        )
      case 4:
        return (
                    <Message
                        title={__('Message')}
                        description={__('Leave a message (optional).')}
                    />
        )
      case 5:
        return (
                    <Summary
                        title={__('Payment')}
                        privacyLink={campaign.privacy_link}
                        termsLink={campaign.terms_link}
                        description={__('By clicking donate you agree to the following payment:')}
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
                                className="text-base"
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
                        className="ml-auto text-base"
                    >
                        {steps[step].name === 'Summary'
                          ? <><LockClosedIcon className="w-5 h-5"/> <span
                                className="mx-2">{__('Submit')}</span></>
                          : <><span className="mx-2">{__('Next')}</span><ChevronRightIcon
                                className="w-5 h-5"/></>}
                    </Button>
                </div>
            </form>
        </FormProvider>
  )
}

export default FormRouter
