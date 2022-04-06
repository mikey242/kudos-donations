import { Fragment, useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { Button, TextControl, ToggleControl } from '../../../../common/components/controls'
import React from 'react'
import Divider from '../../../components/Divider'

const EmailTab = () => {
  return (
        <Fragment>
            <ToggleControl
                label={__('Send email receipts', 'kudos-donations')}
                help={__(
                  'Once a payment has been completed, you can automatically send an email receipt to the donor.',
                  'kudos-donations'
                )}
                name="_kudos_email_receipt_enable"
            />
            <TextControl
                label={__('Send receipt copy to:', 'kudos-donations')}
                help={__('Leave blank to disable.', 'kudos-donations')}
                name="_kudos_email_bcc"
            />
            <Divider/>
            {/* <br/> */}
            {/* <Button */}
            {/*    isLink */}
            {/*    aria-label={__('Refresh API')} */}
            {/*    onClick={check} */}
            {/* > */}
            {/*    <><RefreshIcon className={`${checkingMollie && 'animate-spin '}w-5 h-5`}/> <span */}
            {/*        className="mx-2">{__('Refresh API', 'kudos-donations')}</span></> */}

            {/* </Button> */}
            {/* <p className="my-2 text-xs text-gray-500"> */}
            {/*    {__('Use this if you have made changes in Mollie such as enabling SEPA Direct Debit or credit card.', 'kudos-donations')} */}
            {/* </p> */}
            {/* <Divider/> */}
            {/* <TextControl name="_kudos_vendor_mollie.live_key" label="Live key"/> */}
            {/* <TextControl name="_kudos_vendor_mollie.test_key" label="Test key"/> */}
        </Fragment>
        // <Panel>
        //     <Fragment>
        //         <CardDivider/>
        //         <TestEmailPanel
        //             handleInputChange={props.handleInputChange}
        //             showNotice={props.showNotice}
        //         />
        //         <CardDivider/>
        //         <EmailCustomPanel
        //             settings={props.settings}
        //             handleInputChange={props.handleInputChange}
        //         />
        //         {props.settings._kudos_smtp_enable
        //           ? <Fragment>
        //                 <CardDivider/>
        //                 <EmailServerPanel
        //                     settings={props.settings}
        //                     handleInputChange={props.handleInputChange}
        //                 />
        //                 <CardDivider/>
        //                 <EmailEncryptionPanel
        //                     settings={props.settings}
        //                     handleInputChange={props.handleInputChange}
        //                 />
        //                 <CardDivider/>
        //                 <EmailAuthenticationPanel
        //                     settings={props.settings}
        //                     handleInputChange={props.handleInputChange}
        //                 />
        //                 <CardDivider/>
        //                 <EmailFromPanel
        //                     settings={props.settings}
        //                     handleInputChange={props.handleInputChange}
        //                 />
        //             </Fragment>
        //           : ''}
        //     </Fragment>
        // </Panel>
  )
}

export
{
  EmailTab
}
