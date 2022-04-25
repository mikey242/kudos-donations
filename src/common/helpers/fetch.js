import apiFetch from '@wordpress/api-fetch'

export async function fetchTransactions (id) {
  return await apiFetch({
    path: `kudos/v1/transaction/${id ?? ''}`,
    method: 'GET'
  })
}

export async function fetchCampaignTransactions (id) {
  return await apiFetch({
    path: `kudos/v1/transaction/campaign/${id ?? ''}`,
    method: 'GET'
  })
}

export async function fetchCampaigns (id) {
  return await apiFetch({
    path: `wp/v2/kudos_campaign/${id ?? ''}`,
    method: 'GET'
  })
}
