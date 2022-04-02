import { CompletedPaymentPanel } from '../Panels/CompletedPaymentPanel'
import { TermsPanel } from '../Panels/TermsPanel'
import { PrivacyPanel } from '../Panels/PrivacyPanel'

import { SpamProtectionPanel } from '../Panels/SpamProtectionPanel'
import Panel from '../../../Components/Panel'
import Divider from '../../../Components/Divider'
import { __ } from '@wordpress/i18n'
import RadioControl from '../../../../common/components/controls/RadioControl'

const CustomizeTab = (props) => {
  return (
        <Panel>
            <div className="p-5">

                <RadioControl
                    name="_kudos_completed_payment"
                    help={__('When the donor returns to your website after completing the payment what do you want to happen?', 'kudos-donations')}
                    selected={props.settings._kudos_completed_payment || 'message'}
                    options={[
                      { label: __('Pop-up message', 'kudos-donations'), value: 'message' },
                      { label: __('Custom return URL', 'kudos-donations'), value: 'url' }
                    ]}
                />
                <CompletedPaymentPanel
                    settings={props.settings}
                    handleInputChange={props.handleInputChange}
                />
                <Divider/>
                <PrivacyPanel
                    settings={props.settings}
                    handleInputChange={props.handleInputChange}
                />
                <Divider/>
                <TermsPanel
                    settings={props.settings}
                    handleInputChange={props.handleInputChange}
                />
                <Divider/>
                <SpamProtectionPanel
                    settings={props.settings}
                    handleInputChange={props.handleInputChange}
                />
            </div>
        </Panel>
  )
}

export { CustomizeTab }
