import React, { createContext, useContext } from 'react';
import { __ } from '@wordpress/i18n';
import { Flex, Spinner } from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export const CampaignContext = createContext(null);

export default function CampaignProvider({ campaignId, children }) {
	const [campaign, setCampaign] = useState(null);
	const [campaignErrors, setCampaignErrors] = useState(null);
	const [isLoading, setIsLoading] = useState(false);

	/**
	 * Fetch campaign by ID first, fallback to slug if not found.
	 * Slug fetch is enabled only if the ID fetch resolves and finds nothing.
	 */
	useEffect(() => {
		if (campaignId) {
			setCampaignErrors(null);
			setIsLoading(true);
			// Fetch campaign by ID
			apiFetch({
				path: `/wp/v2/kudos_campaign/${campaignId}`,
			})
				.then((postById) => {
					setCampaign(postById);
				})
				.catch((error) => {
					// Most likely the post ID was not found. Try searching by slug instead.
					if (error?.data?.status === 404) {
						apiFetch({
							path: `/wp/v2/kudos_campaign?slug=${campaignId}`,
						}).then((postBySlug) => {
							if (postBySlug.length > 0) {
								setCampaign(postBySlug[0]);
							} else {
								// If neither ID nor slug finds a campaign, set the error.
								setCampaignErrors([
									__('Campaign not found', 'kudos-donations'),
								]);
							}
						});
					}
				})
				.finally(() => {
					setIsLoading(false);
				});
		}
	}, [campaignId]);

	const contextValue = useMemo(
		() => ({
			campaign,
			campaignErrors,
		}),
		[campaign, campaignErrors]
	);

	if (isLoading) {
		return (
			<Flex justify="center">
				<Spinner />
			</Flex>
		);
	}

	return (
		<CampaignContext.Provider value={contextValue}>
			{children}
		</CampaignContext.Provider>
	);
}

export const useCampaignContext = () => useContext(CampaignContext);
