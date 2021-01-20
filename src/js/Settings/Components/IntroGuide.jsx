import intro from "../../../img/guide-welcome.png"
import mollie from "../../../img/guide-mollie-api.png"
import campaign from "../../../img/guide-campaign.png"
import button from "../../../img/guide-button.png"
import live from "../../../img/guide-test-live.png"

const { __ } = wp.i18n;
const { Guide } = wp.components;

const IntroGuide = ({ show, updateSetting } ) => {

    if ( ! show ) {
        return null;
    }

    const closeModal = () => {
        updateSetting('_kudos_show_intro', false)
    }

    return (
        <Guide
            className={"kudos-intro-guide"}
            onFinish={ () => closeModal() }
            pages={ [
                {
                    image: <img src={intro} />,
                    content:
                    <div className="kd-p-4">
                        <h1 className="kd-leading-normal">Welcome to Kudos Donations</h1>
                        <p>It is easy to get started with Kudos Donations. Complete these simple steps to get your donations started.</p>
                    </div>
                    ,
                },
                {
                    image: <img src={mollie} />,
                    content:
                        <div className="kd-p-4">
                            <h1 className="kd-leading-normal">Connect with Mollie</h1>
                            <p>Log-in to your Mollie account and grab your <a target="_blank" href="https://mollie.com/dashboard/developers/api-keys">api keys</a>.</p>
                            <p>Make sure you get both your <strong>test</strong> and <strong>live</strong> api keys. Then enter them in the relevant fields.</p>
                        </div>
                    ,
                },
                {
                    image: <img src={campaign} />,
                    content:
                        <div className="kd-p-4">
                            <h1 className="kd-leading-normal">Set up a campaign</h1>
                            <p>Visit the <strong>Campaigns</strong> tab and either create a new campaign or edit the default one. </p>
                            <p>Don't forget to click <strong>Copy shortcode</strong> at the bottom of your campaign.</p>
                        </div>
                    ,
                },
                {
                    image: <img src={button} />,
                    content:
                        <div className="kd-p-4">
                            <h1 className="kd-leading-normal">Place a button</h1>
                            <p>Using the Kudos Donations block or shortcode, place the button anywhere on your website. If using the block, you will need to select the campaign you want it to use.</p>
                        </div>
                    ,
                },
                {
                    image: <img src={live} />,
                    content:
                        <div className="kd-p-4">
                            <h1 className="kd-leading-normal">Test and go Live</h1>
                            <p>With the API mode still on <strong>Test</strong> make a payment using your button. If it all looks good then you can switch to <strong>Live</strong>.</p>
                        </div>
                    ,
                },
            ] }
        />
    );
};

export { IntroGuide };
