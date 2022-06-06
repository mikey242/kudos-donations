import apiFetch from '@wordpress/api-fetch';

export async function fetchCampaignTransactions(id) {
	return await apiFetch({
		path: `kudos/v1/transaction/campaign/${id ?? ''}`,
		method: 'GET',
	});
}

export async function fetchCampaigns(id) {
	return await apiFetch({
		path: `wp/v2/kudos_campaign/${id ?? ''}`,
		method: 'GET',
	});
}

export async function fetchTestMollie() {
	return await apiFetch({
		path: 'kudos/v1/payment/test',
		method: 'GET',
	});
}

export async function fetchTestEmail(email) {
	return await apiFetch({
		path: 'kudos/v1/email/test',
		headers: {
			Accept: 'application/json',
			'Content-Type': 'application/json',
		},
		method: 'POST',
		body: JSON.stringify({ email }),
	});
}
