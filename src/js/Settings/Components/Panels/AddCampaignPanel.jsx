import {SettingCard} from "../SettingCard"

const { __, sprintf } = wp.i18n
const { Button, TextControl } = wp.components
const { useState } = wp.element

const AddCampaignPanel = ({addCampaign}) => {

    const [addFormValue, setAddFormValue] = useState('')
    const [buttonDisabled, setButtonDisabled] = useState(true)

    const updateValue = (value) => {
        setAddFormValue(value)
        setButtonDisabled( '' === value.trim() )
    }

    return (
        <SettingCard title={__('New Campaign', 'kudos-donations')}>
            <TextControl
                label={__(
                    'Campaign name',
                    'kudos-donations'
                )}
                help={__('Give your campaign a unique name to identify it.', 'kudos-donations')}
                id={'kudos_new_campaign'}
                type={'text'}
                value={addFormValue}
                onChange={(newValue) => updateValue(newValue)}
            />
            <Button
                isSecondary
                disabled={buttonDisabled}
                onClick={
                    () => addCampaign(document.getElementById('kudos_new_campaign').value)
                }
            >
                {__('Add campaign', 'kudos-donations')}
            </Button>
        </SettingCard>
    )
}

export {AddCampaignPanel}
