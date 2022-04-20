import React from 'react'
import Panel from './Panel'
import { __ } from '@wordpress/i18n'
import { DuplicateIcon, PencilAltIcon, TrashIcon } from '@heroicons/react/outline'

function CampaignTable ({ campaigns, transactions, editClick, duplicateClick, deleteClick }) {
  const getTotal = (campaignId) => {
    const filtered = transactions.filter(transaction => (
      transaction.campaign_id === campaignId
    ))
    if (filtered.length) {
      return filtered.reduce((a, b) => a + parseInt(b.value), 0)
    }
    return 0
  }

  return (
        <Panel className="overflow-x-auto" title={__('Your campaigns', 'kudos-donations')}>
            <table className="min-w-full text-left divide-y divide-gray-300">
                <thead className="bg-gray-50">
                <tr>
                    <th scope="col"
                        className="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">
                        Campaign name
                    </th>
                    <th scope="col"
                        className="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">
                        Color
                    </th>
                    <th scope="col"
                        className="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">
                        Total
                    </th>
                    <th scope="col"
                        className="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">
                        Goal
                    </th>
                    <th scope="col"
                        className="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span className="sr-only">Edit</span>
                    </th>
                </tr>
                </thead>
                <tbody className="divide-y divide-gray-200 bg-white">
                {campaigns?.map((campaign, i) => (
                    <tr key={campaign.id}>
                        <td scope="row"
                            className="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                            {campaign.title.rendered}
                        </td>
                        <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            <div className="w-5 h-5 rounded-full" style={{ backgroundColor: campaign.meta.theme_color }}/>
                        </td>
                        <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{'€' + getTotal(campaign.slug)}</td>
                        <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            {campaign.meta.goal > 0 ? campaign.meta.goal : 'None'}
                        </td>
                        <td className="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <span title={__('Edit campaign', 'kudos-donations')}>
                                <PencilAltIcon
                                    className="h-5 w-5 cursor-pointer mx-1 font-medium inline-block text-gray-500"
                                    onClick={() => editClick(campaign)}
                                />
                            </span>
                            <span title={__('Duplicate campaign', 'kudos-donations')}>
                            <DuplicateIcon
                                className="h-5 w-5 cursor-pointer mx-1 font-medium inline-block text-gray-500"
                                onClick={() => duplicateClick(campaign)}
                            />
                                </span>
                            <span title={__('Delete campaign', 'kudos-donations')}>
                                <TrashIcon
                                    className="h-5 w-5 cursor-pointer mx-1 font-medium inline-block text-red-500"
                                    onClick={() => {
                                      window.confirm(__('Are you sure you wish to delete this campaign?')) &&
                                        deleteClick(campaign.id)
                                    }}
                                />
                            </span>
                        </td>
                    </tr>
                ))}

                </tbody>
            </table>
        </Panel>

  )
}

export default CampaignTable
