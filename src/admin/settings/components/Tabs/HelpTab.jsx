import { Fragment } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import logo from '../../../../images/full-logo-green.svg'
import React from 'react'
import { Button } from '../../../../common/components/controls'
import { InformationCircleIcon, PencilAltIcon, QuestionMarkCircleIcon, SupportIcon } from '@heroicons/react/outline'

const HelpTab = ({ setShowIntro }) => {
  return (
        <Fragment>
            <h2>{__('Share the love', 'kudos-donations')}</h2>
            <p className="mb-2">{__('Do you like using Kudos? Please let us know your thoughts.', 'kudos-donations')}</p>

            <Button
                isOutline
                href="https://wordpress.org/support/plugin/kudos-donations/reviews/#new-post"
                target="_blank"
            >
                <PencilAltIcon className="w-5 h-5 mr-2"/>
                {__('Leave a review', 'kudos-donations')}
            </Button>
            <hr className="my-5"/>
            <h2>{__('Need some assistance?', 'kudos-donations')}</h2>
            <p>{__("Don't hesitate to get in touch if you need any help or have a suggestion.", 'kudos-donations')}</p>
            <div className="flex mt-2">
                <div className="flex flex-grow">
                    <Button
                        isOutline
                        className="mr-2"
                        href="https://wordpress.org/support/plugin/kudos-donations/"
                        target="_blank"
                    >
                        <SupportIcon className="w-5 h-5 mr-2"/>
                        {__('Support forums', 'kudos-donations')}
                    </Button>
                    <Button
                        isOutline
                        className={'mr-2'}
                        onClick={() => setShowIntro(true)}
                    >
                        <InformationCircleIcon className="w-5 h-5 mr-2"/>
                        {__('Show welcome guide', 'kudos-donations')}
                    </Button>
                    <Button
                        isOutline
                        className={'mr-2'}
                        target="_blank"
                        href="https://kudosdonations.com/faq/"
                    >
                        <QuestionMarkCircleIcon className="w-5 h-5 mr-2"/>
                        {__('Visit our F.A.Q', 'kudos-donations')}
                    </Button>
                </div>
                <div>
                    <a target="_blank" title={__('Visit Kudos Donations', 'kudos-donations')} className="block"
                       href="https://kudosdonations.com" rel="noreferrer">
                        <img width="140" src={logo} className="mr-4" alt="Kudos Logo"/>
                    </a>
                </div>
            </div>
            {/* <Panel> */}
            {/*    <HelpPanel */}
            {/*        handleInputChange={props.handleInputChange} */}
            {/*    /> */}
            {/* </Panel> */}
            {/* <Panel> */}
            {/*    <ExportSettingsPanel */}
            {/*        settings={props.settings} */}
            {/*    /> */}
            {/*    <CardDivider/> */}
            {/*    <ImportSettingsPanel */}
            {/*        updateAll={props.updateAll} */}
            {/*        handleInputChange={props.handleInputChange} */}
            {/*    /> */}
            {/*    <CardDivider/> */}
            {/*    <RenderModalFooter */}
            {/*        settings={props.settings} */}
            {/*        handleInputChange={props.handleInputChange} */}
            {/*    /> */}
            {/*    <CardDivider/> */}
            {/*    <DebugModePanel */}
            {/*        settings={props.settings} */}
            {/*        handleInputChange={props.handleInputChange} */}
            {/*    /> */}
            {/*    <CardDivider/> */}
            {/*    <DisableShortcodePanel */}
            {/*        settings={props.settings} */}
            {/*        handleInputChange={props.handleInputChange} */}
            {/*    /> */}
            {/* </Panel> */}
        </Fragment>
  )
}

export { HelpTab }
