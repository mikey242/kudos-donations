import React from 'react';
import {
	createContext,
	useContext,
	useEffect,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useEntityRecord } from '@wordpress/core-data';
import { Flex, Spinner } from '@wordpress/components';

export const CampaignContext = createContext(null);

export default function CampaignProvider({ campaignId, children }) {
	const [campaign, setCampaign] = useState({});
	const [campaignErrors, setCampaignErrors] = useState(null);
	const { record, hasResolved } = useEntityRecord(
		'postType',
		'kudos_campaign',
		campaignId
	);

	useEffect(() => {
		if (hasResolved) {
			if (record) {
				setCampaign(record);
			} else {
				setCampaignErrors([__('Campaign not found')]);
			}
		}
	}, [record, hasResolved]);

	if (!hasResolved) {
		return (
			<Flex justify="center">
				<Spinner />
			</Flex>
		);
	}

	return (
		<>
			<CampaignContext.Provider
				value={{
					campaign,
					hasResolved,
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
