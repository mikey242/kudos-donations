import intro from "../../../images/guide-welcome.png"
import mollie from "../../../images/guide-mollie-api.png"
import campaign from "../../../images/guide-campaign.png"
import button from "../../../images/guide-button.png"
import live from "../../../images/guide-test-live.png"
import {__} from "@wordpress/i18n"
import {Dashicon, ExternalLink, TextControl} from "@wordpress/components"
import {useState} from '@wordpress/element'
import {Guide} from "./Guide"
import {Btn} from "./Btn"

const IntroGuide = ({settings, updateSetting, isAPISaving, updateAll, handleInputChange, mollieChanged}) => {

    const [apiMessage, setApiMessage] = useState(null)
    const vendorMollie = settings._kudos_vendor_mollie
    const isConnected = vendorMollie.connected ?? false
    const isRecurringEnabled = vendorMollie.recurring ?? false

    const closeModal = () => {
        updateSetting('_kudos_show_intro', false)
    }

    const checkApi = () => {
        mollieChanged()
        updateAll(false, (response) => {
            setApiMessage(response.data.data.message)
        })
    }

    return (
        <Guide
            className={"kudos-intro-guide kd-box-border"}
            onFinish={() => closeModal()}
            pages={[
                {
                    imageSrc: intro,
                    heading: __('Welcome to Kudos Donations', 'kudos-donations'),
                    content:
                        <div className={"kd-text-center"}>
                            <p>{__('Complete these simple steps to set up your first donation campaign. Click the "next" button to get started', 'kudos-donations')}</p>
                        </div>
                    ,
                },
                {
                    imageSrc: mollie,
                    nextDisabled: !vendorMollie.connected,
                    heading: __('Connect with Mollie', 'kudos-donations'),
                    content:
                        <div className={"kd-text-center"}>
                            {!isConnected
                                ?
                                <>
                                    <p>
                                        {__('Login to your Mollie account and grab your API keys. Make sure you get both your test and live API keys.', 'kudos-donations')}
                                        {" "}
                                        <ExternalLink
                                            href="https://mollie.com/dashboard/developers/api-keys">{__('Mollie dashboard', 'kudos-donations')}
                                        </ExternalLink>
                                    </p>
                                    <div className={"kd-p-5 kd-bg-white kd-rounded-lg kd-shadow-md" + (isAPISaving ? " kd-opacity-50" : "")}>
                                        <TextControl
                                            key={"_kudos_mollie_live_api_key"}
                                            className={"kd-text-left"}
                                            disabled={isAPISaving}
                                            label={__('Live key', 'kudos-donations')}
                                            value={vendorMollie['live_key'] || ''}
                                            placeholder={__('Begins with "live_"', 'kudos-donations')}
                                            onChange={(value) => handleInputChange('_kudos_vendor_mollie', {
                                                ...vendorMollie,
                                                live_key: value
                                            })}
                                        />
                                        <TextControl
                                            key={"_kudos_mollie_test_api_key"}
                                            className={"kd-text-left"}
                                            disabled={isAPISaving}
                                            label={__('Test key', 'kudos-donations')}
                                            value={vendorMollie['test_key'] || ''}
                                            placeholder={__('Begins with "test_"', 'kudos-donations')}
                                            onChange={(value) => handleInputChange('_kudos_vendor_mollie', {
                                                ...vendorMollie,
                                                test_key: value
                                            })}
                                        />
                                    </div>
                                    <br/>
                                    <Btn
                                        isPrimary
                                        isBusy={isAPISaving}
                                        onClick={() => checkApi()}
                                    >
                                        {__('Connect with Mollie', 'kudos-donations')}
                                    </Btn>
                                    <div className="kd-mt-3 kd-text-base" style={{
                                        color: 'red'
                                    }}>
                                        {apiMessage}
                                    </div>
                                </>
                                :
                                <div className="kd-flex kd-flex-col kd-rounded-lg kd-p-5">
                                    <div className={"kd-flex kd-flex-row kd-justify-center kd-mb-3 kd-items-center"}>
                                        <Dashicon className={"kd-w-auto kd-h-auto kd-text-4xl kd-text-green-500"}
                                                  icon="yes"/>
                                        <h2 className={"kd-m-0 kd-text-green-500"}>{__('Connected')} ({isRecurringEnabled ? __('recurring enabled', 'kudos-donations') : __('recurring not available', 'kudos-donations')})</h2>
                                    </div>
                                    {isRecurringEnabled
                                        ?
                                        <strong>{__('Congratulations, your account is configured to allow recurring payments.', 'kudos-donations')}<ExternalLink
                                                href={"https://help.mollie.com/hc/articles/214558045"}>
                                            {__('Learn more', 'kudos-donations')}
                                            </ExternalLink></strong>
                                        :
                                        <strong>{__('You can still use Kudos, however you will not be able to use subscription payments.', 'kudos-donations')}
                                            <ExternalLink
                                                href={"https://help.mollie.com/hc/articles/214558045"}>
                                                {__('Learn more', 'kudos-donations')}
                                            </ExternalLink></strong>
                                    }
                                </div>
                            }
                        </div>

                    ,
                },
                {
                    imageSrc: campaign,
                    heading: __('Set up a campaign', 'kudos-donations'),
                    content:
                        <div className={"kd-text-center"}>
                            <p>{__('Visit the Campaigns tab and either create a new campaign or edit the default one.', 'kudos-donations')}</p>
                            <p>{__('If you need it, don\'t forget to click "Copy shortcode" at the bottom of your campaign.', 'kudos-donations')}</p>
                        </div>
                    ,
                },
                {
                    imageSrc: button,
                    heading: __('Place a button', 'kudos-donations'),
                    content:
                        <div className={"kd-text-center"}>
                            <p>{__('Use the Kudos Button block or shortcode to place the button anywhere on your website.', 'kudos-donations')}</p>
                            <p>{__('If using the block, select the desired campaign in the block side bar.', 'kudos-donations')}</p>
                        </div>
                    ,
                },
                {
                    imageSrc: live,
                    heading: __('Test and go Live', 'kudos-donations'),
                    content:
                        <div className={"kd-text-center"}>
                            <p>{__('With the API mode still on "Test" make a payment using your button. If it all looks good then you can switch to "Live".', 'kudos-donations')}</p>
                            <p>{__('Good luck with your campaign!', 'kudos-donations')}</p>
                            <p><ExternalLink
                                href="https://kudosdonations.com/faq/">{__('Visit our F.A.Q', 'kudos-donations')}</ExternalLink>
                            </p>
                        </div>
                    ,
                },
            ]}
        />
    )
}

export {IntroGuide}
