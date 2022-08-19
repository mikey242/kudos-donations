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
	const [campaignRequest, setCampaignRequest] = useState({
		ready: false,
		campaign: null,
	});
	const [campaignErrors, setCampaignErrors] = useState(null);

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
			})
			.catch((error) => {
				throw {
					message: `Failed to fetch campaign '${campaignId}'.`,
					original: error,
				};
			});
	};

	const getData = () => {
		Promise.all([getCampaign(), getTotal()])
			.then((data) =>
				setCampaignRequest({
					ready: true,
					campaign: {
						...data[0],
						total: data[1],
					},
				})
			)
			.catch((error) => {
				setCampaignErrors([error.message]);
			});
	};

	const renderApiErrors = () => (
		<>
			<p className="m-0">Kudos Donations ran into a problem:</p>
			{campaignErrors.map((error, i) => (
				<p key={i} className="text-red-500">
					- {error}
				</p>
			))}
		</>
	);

	return (
		<CampaignContext.Provider
			value={{
				campaignRequest,
				campaignId,
				campaignErrors,
			}}
		>
			{!campaignErrors ? children : renderApiErrors()}
		</CampaignContext.Provider>
	);
}

export const useCampaignContext = () => {
	return useContext(CampaignContext);
};
