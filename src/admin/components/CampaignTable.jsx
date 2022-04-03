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
            <table className="w-full text-left text-gray-500 border-collapse p-2">
                <thead className="text-md text-gray-700 bg-gray-100">
                <tr>
                    <th scope="col" className="px-6 py-3">
                        Campaign name
                    </th>
                    <th scope="col" className="px-6 py-3">
                        Color
                    </th>
                    <th scope="col" className="px-6 py-3">
                        Total
                    </th>
                    <th scope="col" className="px-6 py-3">
                        Goal
                    </th>
                    <th scope="col" className="px-6 py-3">
                        <span className="sr-only">Edit</span>
                    </th>
                </tr>
                </thead>
                <tbody>
                {campaigns?.map((campaign, i) => (
                    <tr key={campaign.id} className="bg-white">
                        <th scope="row" className="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                            {campaign.title.rendered}
                        </th>
                        <td className="px-6 py-4">
                            <div className="w-5 h-5 rounded" style={{ backgroundColor: campaign.meta.theme_color }}/>
                        </td>
                        <td className="px-6 py-4">
                            {getTotal(campaign.slug)}
                        </td>
                        <td className="px-6 py-4">
                            {campaign.meta.goal > 0 ? campaign.meta.goal : 'None'}
                        </td>
                        <td className="px-6 py-4 text-right">
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
                            {i !== 0 &&
                                <span title={__('Delete campaign', 'kudos-donations')}>
                                <TrashIcon
                                    className="h-5 w-5 cursor-pointer mx-1 font-medium inline-block text-red-500"
                                    onClick={() => {
                                      window.confirm(__('Are you sure you wish to delete this campaign?')) &&
                                        deleteClick(campaign.id)
                                    }}
                                />
                                </span>
                            }
                        </td>
                    </tr>
                ))}

                </tbody>
            </table>
        </Panel>

  )
}

export default CampaignTable
