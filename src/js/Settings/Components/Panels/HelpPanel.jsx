import logo from '../../../../img/full-logo-green.svg'
import {SettingCard} from "../SettingCard"
import {ButtonIcon} from "../ButtonIcon"

const {__} = wp.i18n
const {
    Button,
} = wp.components

const HelpPanel = ({handleInputChange}) => {

    return (
        <SettingCard>
            <h2>{__('Share the love', 'kudos-donations')}</h2>
            <p>{__('Do you like using Kudos? Please let us know your thoughts.', 'kudos-donations')}</p>

            <Button
                isSecondary
                href="https://wordpress.org/support/plugin/kudos-donations/reviews/#new-post"
                target="_blank"
                icon={(<ButtonIcon icon='quill'/>)}
            >
                {__('Leave a review', 'kudos-donations')}
            </Button>

            <h2>{__('Need some assistance?', 'kudos-donations')}</h2>
            <p>{__("Don't hesitate to get in touch if you need any help or have a suggestion.", 'kudos-donations')}</p>

            <div className="kd-flex">
                <div className="kd-flex-grow">
                    <Button
                        isSecondary
                        className={"kd-mr-2"}
                        href="https://wordpress.org/support/plugin/kudos-donations/"
                        target="_blank"
                        icon={(<ButtonIcon icon='help'/>)}
                    >
                        {__('Support forums', 'kudos-donations')}
                    </Button>
                    <Button
                        isSecondary
                        className={"kd-mr-2"}
                        icon={(<ButtonIcon icon='door'/>)}
                        onClick={() => {
                            handleInputChange('_kudos_show_intro', true, false)
                        }}
                    >
                        {__('Show welcome guide', 'kudos-donations')}
                    </Button>
                    <Button
                        isSecondary
                        className={"kd-mr-2"}
                        target="_blank"
                        href="https://kudosdonations.com/faq/"
                        icon={(<ButtonIcon icon='question'/>)}
                    >
                        {__('Visit our F.A.Q', 'kudos-donations')}
                    </Button>
                </div>
                <div>
                    <a target="_blank" title={__('Visit Kudos Donations', 'kudos-donations')} className="kd-block" href="https://kudosdonations.com">
                        <img width="140" src={logo} className="kd-mr-4" alt="Kudos Logo"/>
                    </a>
                </div>
            </div>
        </SettingCard>
    )
}

export {HelpPanel}
