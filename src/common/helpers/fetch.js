import apiFetch from '@wordpress/api-fetch';

export async function fetchCampaigns(id) {
	return await apiFetch({
		path: `wp/v2/kudos_campaign/${id ?? ''}`,
		method: 'GET',
	});
}
