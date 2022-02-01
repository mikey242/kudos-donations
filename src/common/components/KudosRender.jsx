import apiFetch from '@wordpress/api-fetch'
import { useEffect, useState } from '@wordpress/element'
import ReactShadowRoot from 'react-shadow-root'
import React from 'react'
import { getStyle } from '../helpers/util'
import { KudosButton } from './KudosButton'
import KudosModal from './KudosModal'
import FormRouter from './FormRouter'

const screenSize = getStyle('--kudos-screen')

function KudosRender ({ label }) {
  const [campaign, setCampaign] = useState()
  const [ready, setReady] = useState(false)
  const [formState, setFormState] = useState({
    currentStep: 1,
    skipSteps: [],
    formData: {
      value: '',
      name: '',
      email: '',
      payment_frequency: 'oneoff'
    }
  })
  const [modalOpen, setModalOpen] = useState(false)
  const style = ':host { all: initial } '

  const toggleModal = () => {
    // Open modal
    if (!modalOpen) {
      setModalOpen(true)
    } else {
      // Close modal
      setModalOpen(false)
      setTimeout(() => {
        setFormState((prev) => ({
          ...prev,
          currentStep: 1,
          formData: {
            value: '',
            name: '',
            email: '',
            payment_frequency: 'oneoff'
          }
        }))
      }, 300)
    }
  }

  const getCampaign = () => {
    apiFetch({
      path: `kudos/v1/campaign/get?${new URLSearchParams({ id: 'default' })}`
    }).then((response) => {
      setCampaign(response)
      setReady(true)
    })
  }

  const getNextStep = (skip) => {
    const { currentStep } = formState
    return skip.includes(currentStep + 1) ? currentStep + 2 : currentStep + 1
  }

  const addSkip = (step) => {
    const { skipSteps } = formState
    if (skipSteps.indexOf(step) === -1) {
      return [...skipSteps, step]
    }
    return skipSteps
  }

  const removeSkip = (step) => {
    const { skipSteps } = formState
    const index = skipSteps.indexOf(step)
    if (index > -1) {
      return [skipSteps.splice(index, 1)]
    }
    return skipSteps
  }

  const getSteps = (data) => (data.payment_frequency === 'recurring'
    ? removeSkip(2)
    : addSkip(2))

  const handlePrev = () => {
    const { currentStep, skipSteps } = formState
    const step = skipSteps.includes(currentStep - 1) ? currentStep - 2 : currentStep - 1
    setFormState((prev) => ({
      ...prev,
      currentStep: step
    }))
  }

  const handleNext = (data) => {
    const skip = getSteps(data)
    setFormState((prev) => ({
      ...prev,
      skipSteps: skip,
      formData: { ...prev.formData, ...data },
      currentStep: getNextStep(skip)
    }))
  }

  const handleKeyPress = (e) => {
    if (e.key === 'Escape' || e.keyCode === 27) toggleModal()
  }

  useEffect(() => {
    getCampaign()
  }, [])

  useEffect(() => {
    if (modalOpen) {
      document.addEventListener('keydown', handleKeyPress, false)
    }
    return () => document.removeEventListener('keydown', handleKeyPress, false)
  }, [modalOpen])

  return (
        <ReactShadowRoot>
            <link rel="stylesheet" href="/wp-content/plugins/kudos-donations/dist/public/kudos-public.css"/>
            <style>{style}</style>
            <KudosButton onClick={toggleModal}>
                {label}
            </KudosButton>
            {ready &&
                (
                    <KudosModal
                        toggle={toggleModal}
                        isOpen={modalOpen}
                    >
                        <FormRouter
                            step={formState.currentStep}
                            campaign={campaign}
                            handleNext={handleNext}
                            handlePrev={handlePrev}
                            formData={formState.formData}
                            title={campaign.modal_title}
                            description={campaign.welcome_text}
                        />
                    </KudosModal>
                )}
        </ReactShadowRoot>
  )
}

export default KudosRender
