import apiFetch from '@wordpress/api-fetch'
import { Fragment, useEffect, useRef, useState } from '@wordpress/element'
import ReactShadowRoot from 'react-shadow-root'
import React from 'react'
import PropTypes from 'prop-types'
import { getStyle } from '../../common/helpers/util'
import { KudosButton } from './KudosButton'
import KudosModal from './KudosModal'
import FormRouter from './FormRouter'
import { checkRequirements } from '../../common/helpers/form'
import { anim } from '../../common/helpers/animate'

const screenSize = getStyle('--kudos-screen')

KudosRender.propTypes = {
  buttonLabel: PropTypes.string,
  root: PropTypes.object
}

function KudosRender ({ buttonLabel, campaignId, root, stylesheet }) {
  const [campaign, setCampaign] = useState()
  const [timestamp, setTimestamp] = useState()
  const [ready, setReady] = useState(false)
  const [errors, setErrors] = useState([])
  const [formState, setFormState] = useState({
    currentStep: 1,
    formData: {}
  })

  const [modalOpen, setModalOpen] = useState(false)
  const modal = useRef(null)
  const style = `:host { 
      all: initial;
  }`

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
    return apiFetch({
      path: `wp/v2/kudos_campaign?${new URLSearchParams({ slug: campaignId })}`,
      method: 'GET'
    }).then((response) => {
      setCampaign(response[0]?.meta)
      setTimestamp(Date.now())
      setReady(true)
    })
  }

  const handlePrev = () => {
    const { currentStep } = formState
    const target = modal.current
    let step = currentStep - 1
    const state = { ...formState.formData, ...campaign }

    // Find next available step.
    while (!checkRequirements(state, step) && step >= 1) {
      step--
    }

    anim(target, () => {
      setFormState((prev) => ({
        ...prev,
        currentStep: step
      }))
    }, ['translate-x-1'])
  }

  const handleNext = (data, step) => {
    const state = { ...data, ...campaign }
    const target = modal.current

    // Find next available step.
    while (!checkRequirements(state, step) && step <= 10) {
      step++
    }

    anim(target, () => {
      setFormState((prev) => ({
        ...prev,
        formData: { ...prev.formData, ...data },
        currentStep: step
      }))
    }, ['-translate-x-1'])
  }

  const handleKeyPress = (e) => {
    if (e.key === 'Escape' || e.keyCode === 27) toggleModal()
  }

  const submitForm = (data) => {
    setErrors([])
    const formData = new FormData()
    formData.append('timestamp', timestamp)
    formData.append('campaign_id', campaign.id)
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
        'Content-Type': 'multipart/tabs-data'
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
        <ReactShadowRoot>
            {ready &&
                <Fragment>
                    <link rel="stylesheet" href={stylesheet.href}/>
                    <style>{style}</style>
                    <style>{`:host {--kudos-theme-primary: ${campaign?.theme_color}`}</style>
                    <div id="kudos" className="font-sans text-base">
                        <KudosButton onClick={toggleModal}>
                            {buttonLabel}
                        </KudosButton>

                        <KudosModal
                            toggle={toggleModal}
                            root={root}
                            ref={modal}
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
                            />
                        </KudosModal>

                    </div>
                </Fragment>
            }
        </ReactShadowRoot>
  )
}

export default KudosRender