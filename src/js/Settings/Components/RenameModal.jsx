const { __ } = wp.i18n
const { Button, Modal, TextControl, Flex, FlexItem } = wp.components
const { useState } = wp.element

const RenameModal = ({ campaign, updateSetting, isCampaignNameValid }) => {

    const id = 'rename' + '-' + campaign.name
    const [ isOpen, setOpen ] = useState( false )
    const [ name, setName ] = useState( '' )
    const [ buttonDisabled, setButtonDisabled ] = useState( true )
    const openModal = () => setOpen( true )
    const closeModal = () => setOpen( false )

    const updateValue = ( value ) => {
        setName(value)
        setButtonDisabled(!isCampaignNameValid(value))
    }

    const rename = ( newName ) => {
        campaign.name = newName
        updateSetting('_kudos_campaigns' )
        closeModal()
    }

    return (
        <>
            <Button isSecondary isSmall onClick={ openModal }>Rename</Button>

            { isOpen && (

                <Modal title={ __('Rename campaign') } onRequestClose={ closeModal }>

                    <TextControl
                        label={ __( 'New name', 'kudos-donations' ) }
                        id={ id }
                        className={ 'kd-inline' }
                        type={ 'text' }
                        value={ name }
                        onChange={ (value) => {
                            updateValue(value)
                        } }
                    />

                    <Flex justify={'flex-end'}>
                        <FlexItem>
                            <Button isSecondary onClick={ closeModal }>
                                { __('Cancel', 'kudos-donations') }
                            </Button>
                        </FlexItem>
                        <FlexItem>
                            <Button
                                isPrimary
                                disabled={ buttonDisabled }
                                onClick={
                                    () => rename(document.getElementById(id).value)
                                }
                            >
                                { __('Rename', 'kudos-donations') }
                            </Button>
                        </FlexItem>
                    </Flex>
                </Modal>

            ) }

        </>
    )
}

export { RenameModal }
