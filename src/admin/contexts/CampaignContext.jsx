import React from 'react';
import {
	createContext,
	useContext,
	useEffect,
	useState,
} from '@wordpress/element';
// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';

export const CampaignContext = createContext(null);

export default function CampaignProvider({ campaignId, children }) {
	const [campaign, setCampaign] = useState(null);
	const [campaignReady, setCampaignReady] = useState(false);
	const [campaignErrors, setCampaignErrors] = useState(null);
	const [total, setTotal] = useState(0);

	useEffect(() => {
		getData();
	}, []);

	const getTotal = () => {
		return apiFetch({
			path: `kudos/v1/transaction/campaign/total/${campaignId}`,
			method: 'GET',
		}).then((response) => {
			return response;
		});
	};

	const getCampaign = () => {
		return apiFetch({
			path: `wp/v2/kudos_campaign/${campaignId}`,
			method: 'GET',
		})
			.then((response) => {
				return response?.meta;
				// setTimestamp(Date.now());
			})
			.catch((error) => {
				throw {
					message: `Failed to fetch campaign '${campaignId}'.`,
					original: error,
				};
			});
	};

	const getData = () => {
		Promise.all([
			getCampaign().then((res) => setCampaign(res)),
			getTotal().then((res) => setTotal(res)),
		])
			.then(() => setCampaignReady(true))
			.catch((error) => {
				setCampaignErrors([error.message]);
			});
	};

	return (
		<CampaignContext.Provider
			value={{
				campaign,
				campaignId,
				total,
				getCampaign,
				getTotal,
				campaignErrors,
				campaignReady,
			}}
		>
			{children}
		</CampaignContext.Provider>
	);
}

export const useCampaignContext = () => {
	return useContext(CampaignContext);
};
