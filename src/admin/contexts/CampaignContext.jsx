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
		campaign: null,
	});
	const [campaignErrors, setCampaignErrors] = useState(false);

	useEffect(() => {
		if (campaignId) {
			getData();
		} else {
			setCampaignErrors([__('No campaign selected.', 'kudos-donations')]);
		}
	}, [campaignId]);

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
					message: `Failed to find campaign with id: '${campaignId}'.`,
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
			})
			.finally(() => {
				setCampaignRequest((prevState) => ({
					ready: true,
					...prevState,
				}));
			});
	};

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
