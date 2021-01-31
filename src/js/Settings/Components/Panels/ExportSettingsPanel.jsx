import {SettingCard} from "../SettingCard"

const {__} = wp.i18n
const {Button, BaseControl} = wp.components

const ExportSettingsPanel = (props) => {

    const exportSettings = () => {

        const url = window.URL.createObjectURL(
            new Blob([JSON.stringify(props.settings)], {
                type: 'application/json',
            })
        )
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', __('kudos-settings') + '.json')
        document.body.appendChild(link)
        link.click()
    }


    return (
        <SettingCard title={__('Export settings', 'kudos-donations')}>
            <BaseControl
                id="export-1"
                help={__('Note: this file will contain sensitive information and should be kept safe.', 'kudos-donations')}
            >
                <Button
                    isLink
                    onClick={() => {
                        exportSettings()
                    }}
                >
                    {__('Download settings as JSON', 'kudos-donations')}
                </Button>
            </BaseControl>
        </SettingCard>
    )
}

export {ExportSettingsPanel}
