import { SettingCard } from '../SettingCard'
import { ButtonIcon } from '../ButtonIcon'
import React from 'react'
import { __ } from '@wordpress/i18n'
import { Button, TextControl } from '@wordpress/components'
import { useState } from '@wordpress/element'

const NewCampaignPanel = ({ addCampaign, isAPISaving }) => {
  const [addFormValue, setAddFormValue] = useState('')
  const [buttonDisabled, setButtonDisabled] = useState(true)

  const updateValue = (value) => {
    setAddFormValue(value)
    setButtonDisabled(value.trim() === '')
  }

  return (
        <SettingCard title={__('New campaign', 'kudos-donations')}>
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
                isBusy={isAPISaving}
                icon={(<ButtonIcon icon="add"/>)}
                onClick={
                    () => {
                      addCampaign(document.getElementById('kudos_new_campaign').value)
                      setButtonDisabled(true)
                    }
                }
            >
                {__('Add campaign', 'kudos-donations')}
            </Button>
        </SettingCard>
  )
}

export { NewCampaignPanel }