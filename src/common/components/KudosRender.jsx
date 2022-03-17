import apiFetch from '@wordpress/api-fetch'
import { useEffect, useRef, useState } from '@wordpress/element'
import ReactShadowRoot from 'react-shadow-root'
import React from 'react'
import { getStyle } from '../helpers/util'
import { KudosButton } from './KudosButton'
import KudosModal from './KudosModal'
import FormRouter from './FormRouter'
import { checkRequirements } from '../helpers/form'

const screenSize = getStyle('--kudos-screen')

function KudosRender ({ label, root }) {
  const [campaign, setCampaign] = useState()
  const [timestamp, setTimestamp] = useState()
  const [ready, setReady] = useState(false)
  const [errors, setErrors] = useState([])
  const [formState, setFormState] = useState({
    currentStep: 1,
    skipSteps: [],
    formData: {}
  })
  const [modalOpen, setModalOpen] = useState(false)
  const modal = useRef(null)
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
          formData: {}
        }))
      }, 300)
    }
  }

  const getCampaign = () => {
    apiFetch({
      path: `kudos/v1/campaign/get?${new URLSearchParams({ id: 'default' })}`
    }).then((response) => {
      setCampaign(response)
      setTimestamp(Date.now())
      setReady(true)
    })
  }

  const handlePrev = () => {
    const { currentStep } = formState
    let step = currentStep - 1
    const state = { ...formState.formData, ...campaign }
    // Find next available step
    while (!checkRequirements(state, step) && step >= 1) {
      step--
    }
    setFormState((prev) => ({
      ...prev,
      currentStep: step
    }))
  }

  const handleNext = (data, step) => {
    const state = { ...data, ...campaign }
    // Find next available step
    while (!checkRequirements(state, step) && step <= 10) {
      step++
    }

    setFormState((prev) => ({
      ...prev,
      formData: { ...prev.formData, ...data },
      currentStep: step
    }))
  }

  const handleKeyPress = (e) => {
    if (e.key === 'Escape' || e.keyCode === 27) toggleModal()
  }

  const submitForm = (data) => {
    setErrors([])
    const formData = new FormData()
    formData.append('timestamp', timestamp)
    for (const key in data) {
      if (key === 'field') {
        formData.append(key, data[key][1])
      } else {
        formData.append(key, data[key])
      }
    }

    apiFetch({
      path: 'kudos/v1/payment/create',
      headers: new Headers({
        'Content-Type': 'multipart/form-data'
      }),
      method: 'POST',
      body: new URLSearchParams(formData)
    }).then((result) => {
      if (result.success) {
        window.location.href = result.data
      } else {
        setErrors([...errors, result.data.message])
      }
    })
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
        <ReactShadowRoot

        >
            <link rel="stylesheet" href="/wp-content/plugins/kudos-donations/dist/public/kudos-public.css"/>
            {/* <style>{style}</style> */}
            <div id="kudos" className="font-sans text-base">
                <KudosButton onClick={toggleModal}>
                    {label}
                </KudosButton>
                {ready &&
                    (
                        <KudosModal
                            toggle={toggleModal}
                            root={root}
                            isOpen={modalOpen}
                        >
                            {errors.length > 0 &&
                                errors.map((e, i) => (
                                    <small className="text-center block font-normal mb-4 text-sm text-red-500"
                                           key={i}>{e}</small>
                                ))
                            }
                            <FormRouter
                                step={formState.currentStep}
                                campaign={campaign}
                                handleNext={handleNext}
                                handlePrev={handlePrev}
                                submitForm={submitForm}
                                formData={formState.formData}
                                title={campaign.modal_title}
                                description={campaign.welcome_text}
                            />
                        </KudosModal>
                    )}
            </div>
        </ReactShadowRoot>
  )
}

export default KudosRender
