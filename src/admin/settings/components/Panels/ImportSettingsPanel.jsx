import { SettingCard } from '../SettingCard'
import { ButtonIcon } from '../ButtonIcon'
import { __ } from '@wordpress/i18n'
import { BaseControl, FormFileUpload } from '@wordpress/components'

const ImportSettingsPanel = (props) => {
  const importSettings = (e) => {
    const file = e.target.files[0]
    if (file) {
      const reader = new FileReader()
      reader.readAsText(file)
      reader.onload = function (event) {
        const contents = event.target.result.toString()
        const obj = JSON.parse(contents)
        Object.keys(obj).forEach(function (k) {
          if (obj[k] !== '') {
            props.handleInputChange(k, obj[k])
          }
        })
        props.updateAll()
      }
    }
  }

  return (
        <SettingCard title={__('Import settings', 'kudos-donations')}>
            <BaseControl
                id="import-1"
                help={__('Note: this will overwrite your current settings.', 'kudos-donations')}
            >
                <FormFileUpload
                    accept="text/*"
                    icon={(<ButtonIcon icon="upload"/>)}
                    className={'is-secondary'}
                    onChange={(e) => importSettings(e)}
                >
                    {__('Upload', 'kudos-donations')}
                </FormFileUpload>
            </BaseControl>
        </SettingCard>
  )
}

export { ImportSettingsPanel }
