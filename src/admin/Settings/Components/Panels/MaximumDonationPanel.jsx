import {SettingCard} from "../SettingCard"
import {__} from "@wordpress/i18n"
import {TextControl} from "@wordpress/components"

const MaximumDonationPanel = (props) => {

    return (
        <SettingCard title={__('Maximum Donation', 'kudos-donations')}>

            <TextControl
                label={__('Amount', 'kudos-donations')}
                help={__('The maximum donation that you want to allow, leave blank to disable. This applies only to the open donation field.', 'kudos-donations')}
                type={'number'}
                value={props.settings._kudos_maximum_donation || ''}
                placeholder={props.placeholder}
                disabled={props.isSaving}
                onChange={(value) => props.handleInputChange('_kudos_maximum_donation', value)}
            />

        </SettingCard>
    )
}

export {MaximumDonationPanel}
