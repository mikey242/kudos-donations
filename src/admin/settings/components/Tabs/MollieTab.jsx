import { __ } from '@wordpress/i18n'
import React from 'react'
import { RadioGroupControl, TextControl } from '../../../../common/components/controls'
import { Fragment, useState } from '@wordpress/element'
import { RefreshIcon } from '@heroicons/react/solid'
import Divider from '../../../components/Divider'
import { fetchTestMollie } from '../../../../common/helpers/fetch'
import classNames from 'classnames'

const MollieTab = ({ createNotification, updateSetting }) => {
  const [checkingMollie, setCheckingMollie] = useState(false)

  async function checkApiKey () {
    setCheckingMollie(true)

    return fetchTestMollie()
      .then((response) => {
        createNotification(response.data.message, response?.success)
        updateSetting('_kudos_vendor_mollie.connected', response?.success)
        return response
      })
      .then(() => setCheckingMollie(false))
  }

  return (
        <Fragment>
            <RadioGroupControl
                name="_kudos_vendor_mollie.mode"
                label={__('API Mode', 'kudos-donations')}
                options={[
                  { label: __('Test', 'kudos-donations'), value: 'test' },
                  { label: __('Live', 'kudos-donations'), value: 'live' }
                ]}
                help={__(
                  'When using Kudos Donations for the first time, the payment mode is set to "Test". Check that the configuration is working correctly. Once you are ready to receive live payments you can switch the mode to "Live".',
                  'kudos-donations'
                )}
            />
            <br/>
            <div
                className="inline-flex items-center cursor-pointer"
                onClick={checkApiKey}
            >
                <><RefreshIcon className={classNames(checkingMollie && 'animate-spin', 'w-5 h-5')}/> <span
                    className="mx-2">{__('Test / Refresh API', 'kudos-donations')}</span></>

            </div>
            <p className="my-2 text-xs text-gray-500">
                {__('Use this if you have made changes in Mollie such as enabling SEPA Direct Debit or credit card.', 'kudos-donations')}
            </p>
            <Divider/>
            <TextControl name="_kudos_vendor_mollie.live_key" label="Live key"/>
            <TextControl name="_kudos_vendor_mollie.test_key" label="Test key"/>
        </Fragment>
  )
}

export default MollieTab
