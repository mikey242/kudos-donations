import { Icon, edit, trash } from '@wordpress/icons'
import React from 'react'
import Panel from './Panel'

function Table ({ campaigns, transactions, editClick, deleteClick }) {
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
        <Panel>
            <table className="w-full text-left text-gray-500 border-collapse">
                <thead className="text-md text-gray-700 bg-gray-100">
                <tr>
                    <th scope="col" className="px-6 py-3">
                        Campaign name
                    </th>
                    <th scope="col" className="px-6 py-3">
                        Campaign Id
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
                {campaigns?.reverse().map((campaign, i) => (
                    <tr key={campaign.id} className="bg-white border-b">
                        <th scope="row" className="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                            {campaign.title.rendered}
                        </th>
                        <td className="px-6 py-4">
                            {campaign.slug}
                        </td>
                        <td className="px-6 py-4">
                            {getTotal(campaign.slug)}
                        </td>
                        <td className="px-6 py-4">
                            {campaign.meta.goal > 0 ? campaign.meta.goal : 'None'}
                        </td>
                        <td className="px-6 py-4 text-right">
                            <a href="#" onClick={() => editClick(campaign)}
                               className="mr-2 font-medium inline-block text-gray-700 hover:underline"><Icon
                                fill={'currentColor'} icon={edit}/></a>
                            {i !== 0 &&
                                <a href="#" onClick={() => deleteClick(campaign.id)}
                                   className="font-medium inline-block text-gray-700 hover:underline"><Icon
                                    fill={'currentColor'} icon={trash}/></a>
                            }
                        </td>
                    </tr>
                ))}

                </tbody>
            </table>
        </Panel>

  )
}

export default Table
