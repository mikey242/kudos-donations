import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {TextControl} from "@wordpress/components"

const TermsPanel = (props) => {

    return (
        <SettingCard title={__('Terms and conditions', 'kudos-donations')}>

            <TextControl
                label={__('URL', 'kudos-donations')}
                help={__('The url containing your terms and conditions for the donation. Leave empty to disable.', 'kudos-donations')}
                type={'text'}
                value={props.settings._kudos_terms_link || ''}
                placeholder={props.placeholder}
                disabled={props.isSaving}
                onChange={(value) => props.handleInputChange('_kudos_terms_link', value)}
            />

        </SettingCard>
    )
}

export {TermsPanel}
