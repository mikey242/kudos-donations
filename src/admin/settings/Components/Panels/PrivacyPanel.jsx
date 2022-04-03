import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {TextControl} from "@wordpress/components"

const PrivacyPanel = (props) => {

    return (
        <SettingCard title={__('Privacy policy', 'kudos-donations')}>

            <TextControl
                label={__('URL', 'kudos-donations')}
                help={__('The url containing your privacy policy. Leave empty to disable.', 'kudos-donations')}
                type={'text'}
                value={props.settings._kudos_privacy_link || ''}
                placeholder={props.placeholder}
                disabled={props.isSaving}
                onChange={(value) => props.handleInputChange('_kudos_privacy_link', value)}
            />

        </SettingCard>
    )
}

export {PrivacyPanel}
