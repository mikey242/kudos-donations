import intro from "../../../img/guide-welcome.png"
import mollie from "../../../img/guide-mollie-api.png"
import campaign from "../../../img/guide-campaign.png"
import button from "../../../img/guide-button.png"
import live from "../../../img/guide-test-live.png"

const {__} = wp.i18n
const {Guide, ExternalLink} = wp.components

const IntroGuide = ({show, updateSetting}) => {

    if (!show) {
        return null
    }

    const closeModal = () => {
        updateSetting('_kudos_show_intro', false)
    }

    return (
        <Guide
            className={"kudos-intro-guide"}
            onFinish={() => closeModal()}
            pages={[
                {
                    image: <img alt="Intro graphic" src={intro}/>,
                    content:
                        <div className="kd-intro-container">
                            <h1 className="kd-leading-normal">{__('Welcome to Kudos Donations', 'kudos-donations')}</h1>
                            <p>{__('It is easy to get started with Kudos Donations.', 'kudos-donations')}</p>
                            <p>{__('Complete these simple steps to set up your first donation campaign.', 'kudos-donations')}</p>
                        </div>
                    ,
                },
                {
                    image: <img alt="Mollie API key graphic" src={mollie}/>,
                    content:
                        <div className="kd-intro-container">
                            <h1 className="kd-leading-normal">{__('Connect with Mollie', 'kudos-donations')}</h1>
                            <p>{__('Login to your Mollie account and grab your API keys.', 'kudos-donations')}</p>
                            <p>{__('Make sure you get both your test and live API keys. Then enter them under the Mollie tab.', 'kudos-donations')}</p>
                            <ExternalLink
                                href="https://mollie.com/dashboard/developers/api-keys">{__('Mollie dashboard', 'kudos-donations')}</ExternalLink>
                        </div>
                    ,
                },
                {
                    image: <img alt="Campaign graphic" src={campaign}/>,
                    content:
                        <div className="kd-intro-container">
                            <h1 className="kd-leading-normal">{__('Set up a campaign', 'kudos-donations')}</h1>
                            <p>{__('Visit the Campaigns tab and either create a new campaign or edit the default one.', 'kudos-donations')}</p>
                            <p>{__('If you need it, don\'t forget to click "Copy shortcode" at the bottom of your campaign.', 'kudos-donations')}</p>
                        </div>
                    ,
                },
                {
                    image: <img alt="Kudos Donations button graphic" src={button}/>,
                    content:
                        <div className="kd-intro-container">
                            <h1 className="kd-leading-normal">{__('Place a button', 'kudos-donations')}</h1>
                            <p>{__('Use the Kudos Button block or shortcode to place the button anywhere on your website.', 'kudos-donations')}</p>
                            <p>{__('If using the block, select the desired campaign in the block side bar.', 'kudos-donations')}</p>
                        </div>
                    ,
                },
                {
                    image: <img alt="Switch for live mode" src={live}/>,
                    content:
                        <div className="kd-intro-container">
                            <h1 className="kd-leading-normal">{__('Test and go Live', 'kudos-donations')}</h1>
                            <p>{__('With the API mode still on "Test" make a payment using your button. If it all looks good then you can switch to "Live".', 'kudos-donations')}</p>
                            <p>{__('Good luck with your campaign!', 'kudos-donations')}</p>
                            <p><ExternalLink href="https://kudosdonations.com/faq/">Visit our F.A.Q</ExternalLink></p>
                        </div>
                    ,
                },
            ]}
        />
    )
}

export {IntroGuide}
