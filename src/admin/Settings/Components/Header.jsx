import React from 'react'
import { KudosLogo } from '../../../common/components/KudosLogo'
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

const Header = (props) => {
  const { children } = props

  return (
        <div
            className="sticky top-0 flex justify-around w-full z-1050 bg-white py-5">
            <div className="max-w-3xl w-full mx-auto flex items-center justify-between">
                <div className="flex">
                    <a title={__('Visit Kudos Donations', 'kudos-donations')} className="flex mr-4 logo-animate"
                       href="https://kudosdonations.com"
                       target="_blank" rel="noreferrer">
                        <KudosLogo/>
                    </a>
                    <h1>{__('Kudos Donations', 'kudos-donations')}</h1>
                    <span
                        className="kudos-version self-center font-bold rounded-lg p-3 ml-4 border-1 border-solid border-gray-400">
						{applyFilters('kudos.settings.headerVersion', window.kudos.version)}
					</span>
                </div>
                <div className="flex items-center">
                    {children}
                </div>
            </div>
        </div>
  )
}

export { Header }
