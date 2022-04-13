import apiFetch from '@wordpress/api-fetch'
import { useEffect, useState } from '@wordpress/element'
import React from 'react'
import PropTypes from 'prop-types'
import KudosModal from './KudosModal'
import KudosRender from './KudosRender'
import { Button } from '../../common/components/controls'
import { __ } from '@wordpress/i18n'

KudosMessage.propTypes = {
  title: PropTypes.string,
  body: PropTypes.node,
  campaignId: PropTypes.string,
  root: PropTypes.object
}

function KudosMessage ({ title, body, campaignId, root }) {
  const [campaign, setCampaign] = useState()
  const [ready, setReady] = useState(false)

  const [modalOpen, setModalOpen] = useState(true)

  const toggleModal = () => {
    setModalOpen(!modalOpen)
  }

  const handleKeyPress = (e) => {
    if (e.key === 'Escape' || e.keyCode === 27) toggleModal()
  }

  const getCampaign = () => {
    return apiFetch({
      path: `wp/v2/kudos_campaign?${new URLSearchParams({ slug: campaignId })}`,
      method: 'GET'
    }).then((response) => {
      setCampaign(response[0]?.meta)
      setReady(true)
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
        <>
            {ready &&
                <KudosRender themeColor={campaign?.theme_color}>
                    <KudosModal
                        toggle={toggleModal}
                        root={root}
                        isOpen={modalOpen}
                    >
                        <h2 className="font-normal font-serif text-4xl m-0 mb-2 text-gray-900 block text-center">{campaign?.return_message_title}</h2>
                        <p className="text-lg text-gray-900 text-center block font-normal mb-4">{campaign?.return_message_text}</p>
                        <Button
                            type="button"
                            className="text-base block ml-auto"
                            ariaLabel={__('Prev')}
                            onClick={toggleModal}
                        >
                            <span className="mx-2">OK</span>
                        </Button>
                    </KudosModal>
                </KudosRender>
            }
        </>
  )
}

export default KudosMessage
