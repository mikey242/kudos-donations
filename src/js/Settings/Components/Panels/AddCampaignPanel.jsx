import {SettingCard} from "../SettingCard"

const { __, sprintf } = wp.i18n
const { Button, TextControl } = wp.components
const { useState } = wp.element

const AddCampaignPanel = ({settings, updateSetting}) => {

    const [addFormValue, setAddFormValue] = useState('')
    const [buttonDisabled, setButtonDisabled] = useState(true)
    let current = settings._kudos_campaigns

    const updateValue = (value) => {
        setAddFormValue(value)
        setButtonDisabled( '' === value.trim() )
    }

    const addCampaign = (name) => {

        // Add new campaign with defaults to top of array using unshift
        current.push({
            name: name,
            modal_title: __('Support us!', 'kudos-donations'),
            welcome_text: __('Your support is greatly appreciated and will help to keep us going.', 'kudos-donations'),
            donation_type: 'both',
            amount_type: 'both',
            fixed_amounts: '5,10,20,50'
        })

        // Save changes and show notice
        updateSetting('_kudos_campaigns', null, sprintf(__('Added campaign "%s".', 'kudos-donations'), name))
        setButtonDisabled(true)
    }

    return (
        <SettingCard title="Add Campaign">
            <TextControl
                label={__(
                    'Campaign name',
                    'kudos-donations'
                )}
                help={__("Give your campaign a unique name to identify it.", 'kudos-donations')}
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
