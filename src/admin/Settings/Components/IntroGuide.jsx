import intro from "../../../images/guide-welcome.png"
import mollie from "../../../images/guide-mollie-api.png"
import campaign from "../../../images/guide-campaign.png"
import button from "../../../images/guide-button.png"
import live from "../../../images/guide-test-live.png"
import {__} from "@wordpress/i18n"
import {ExternalLink, TextControl} from "@wordpress/components"
import {useState} from '@wordpress/element'
import {Guide} from "./Guide"
import {Btn} from "./Btn"

const IntroGuide = ({settings, updateSetting, isAPISaving, updateAll, handleInputChange, mollieChanged}) => {

    const [apiMessage, setApiMessage] = useState(null)
    const vendorMollie = settings._kudos_vendor_mollie
    const isConnected = vendorMollie.connected ?? false

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
                    content:
                        <div>
                            <h1 className="kd-leading-normal">{__('Welcome to Kudos Donations', 'kudos-donations')}</h1>
                            <p>{__('It is easy to get started with Kudos Donations.', 'kudos-donations')}</p>
                            <p>{__('Complete these simple steps to set up your first donation campaign.', 'kudos-donations')}</p>
                        </div>
                    ,
                },
                {
                    imageSrc: mollie,
                    nextDisabled: !vendorMollie.connected,
                    content:
                        <div>
                            <h1 className="kd-leading-normal">{__('Connect with Mollie', 'kudos-donations')}</h1>

                            <p>
                                {__('Login to your Mollie account and grab your API keys. Make sure you get both your test and live API keys.', 'kudos-donations')}
                                {" "}
                                <ExternalLink
                                    href="https://mollie.com/dashboard/developers/api-keys">{__('Mollie dashboard', 'kudos-donations')}
                                </ExternalLink>
                            </p>
                            { ! isConnected
                                ?
                                <>
                                    <TextControl
                                        key={"_kudos_mollie_live_api_key"}
                                        type={'text'}
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
                                        type={'text'}
                                        label={__('Test key', 'kudos-donations')}
                                        value={vendorMollie['test_key'] || ''}
                                        placeholder={__('Begins with "test_"', 'kudos-donations')}
                                        onChange={(value) => handleInputChange('_kudos_vendor_mollie', {
                                            ...vendorMollie,
                                            test_key: value
                                        })}
                                    />
                                    <br/>
                                    <Btn
                                        isPrimary
                                        // disabled={vendorMollie.connected}
                                        isBusy={isAPISaving}
                                        onClick={() => checkApi()}
                                    >
                                        {__('Connect with Mollie', 'kudos-donations')}
                                    </Btn>
                                    <span className="kd-ml-3" style={{
                                        color: 'red'
                                    }}>
                                                {apiMessage}
                                        </span>
                                </>
                                :
                                <h2 style={{
                                    color: 'green'
                                }}>
                                    Mollie connected!
                                </h2>
                            }
                        </div>

                    ,
                },
                {
                    imageSrc: campaign,
                    content:
                        <div>
                            <h1 className="kd-leading-normal">{__('Set up a campaign', 'kudos-donations')}</h1>
                            <p>{__('Visit the Campaigns tab and either create a new campaign or edit the default one.', 'kudos-donations')}</p>
                            <p>{__('If you need it, don\'t forget to click "Copy shortcode" at the bottom of your campaign.', 'kudos-donations')}</p>
                        </div>
                    ,
                },
                {
                    imageSrc: button,
                    content:
                        <div>
                            <h1 className="kd-leading-normal">{__('Place a button', 'kudos-donations')}</h1>
                            <p>{__('Use the Kudos Button block or shortcode to place the button anywhere on your website.', 'kudos-donations')}</p>
                            <p>{__('If using the block, select the desired campaign in the block side bar.', 'kudos-donations')}</p>
                        </div>
                    ,
                },
                {
                    imageSrc: live,
                    content:
                        <div>
                            <h1 className="kd-leading-normal">{__('Test and go Live', 'kudos-donations')}</h1>
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
