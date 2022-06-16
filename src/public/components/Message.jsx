import {useEffect, useState} from '@wordpress/element'
import React from 'react'
import PropTypes from 'prop-types'
import KudosModal from '../../common/components/KudosModal'
import Render from './Render'
import {Button} from '../../common/components/controls'
import {__} from '@wordpress/i18n'

Message.propTypes = {
    title: PropTypes.string,
    body: PropTypes.node,
    color: PropTypes.string,
    root: PropTypes.object,
}

const stylesheet = document.getElementById('kudos-donations-public-css')

function Message({title, body, color, root}) {
    const [ready, setReady] = useState(false)

    const [modalOpen, setModalOpen] = useState(true)

    const toggleModal = () => {
        setModalOpen(!modalOpen)
    }

    const handleKeyPress = (e) => {
        if (e.key === 'Escape' || e.keyCode === 27) toggleModal()
    }

    useEffect(() => {
        setReady(true)
    }, [])

    useEffect(() => {
        if (modalOpen) {
            document.addEventListener('keydown', handleKeyPress, false)
        }
        return () =>
            document.removeEventListener('keydown', handleKeyPress, false)
    }, [modalOpen])

    return (
        <>
            {ready && (
                <Render themeColor={color} stylesheet={stylesheet.href}>
                    <KudosModal
                        toggle={toggleModal}
                        root={root}
                        isOpen={modalOpen}
                    >
                        <>
                            <h2 className="font-normal font-serif text-4xl m-0 mb-2 text-gray-900 block text-center">
                                {title}
                            </h2>
                            <p className="text-lg text-gray-900 text-center block font-normal mb-4">
                                {body}
                            </p>
                            <Button
                                type="button"
                                className="text-base block ml-auto"
                                ariaLabel={__('Prev', 'kudos-donations')}
                                onClick={toggleModal}
                            >
                                <span className="mx-2">OK</span>
                            </Button>
                        </>
                    </KudosModal>
                </Render>
            )}
        </>
    )
}

export default Message
