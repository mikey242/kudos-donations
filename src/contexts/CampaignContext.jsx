import React from 'react';
import {
	createContext,
	useContext,
	useEffect,
	useState,
} from '@wordpress/element';
// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

export const CampaignContext = createContext(null);

export default function CampaignProvider({ campaignId, children }) {
	const [campaignRequest, setCampaignRequest] = useState({
		ready: false,
		campaign: {},
	});
	const [campaignErrors, setCampaignErrors] = useState(null);

	useEffect(() => {
		const getCampaign = () => {
			return apiFetch({
				path: `wp/v2/kudos_campaign/${campaignId}`,
				method: 'GET',
			}).catch((error) => {
				setCampaignErrors([error.message]);
			});
		};

		if (campaignId) {
			getCampaign().then((response) => {
				setCampaignRequest({
					ready: true,
					campaign: { ...response },
				});
			});
		} else {
			setCampaignErrors([__('No campaign selected.', 'kudos-donations')]);
		}
	}, [campaignId]);

	return (
		<>
			<CampaignContext.Provider
				value={{
					campaignRequest,
					campaignId,
					campaignErrors,
				}}
			>
				{children}
			</CampaignContext.Provider>
		</>
	);
}

export const useCampaignContext = () => {
	return useContext(CampaignContext);
};
