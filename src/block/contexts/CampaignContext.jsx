import React, { createContext, useContext } from 'react';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Flex, Spinner } from '@wordpress/components';

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

			// Try by ID
			apiFetch({ path: `/wp/v2/kudos_campaign/${campaignId}` })
				.then((postById) => {
					setCampaign(postById);
					setIsLoading(false);
				})
				.catch((error) => {
					if (error?.data?.status === 404) {
						// Try by slug
						apiFetch({
							path: `/wp/v2/kudos_campaign?slug=${campaignId}`,
						})
							.then((postBySlug) => {
								if (postBySlug.length > 0) {
									setCampaign(postBySlug[0]);
								} else {
									setCampaignErrors([
										__(
											'Campaign not found',
											'kudos-donations'
										),
									]);
								}
							})
							.catch(() => {
								setCampaignErrors([
									__(
										'Failed to fetch campaign by slug',
										'kudos-donations'
									),
								]);
							})
							.finally(() => {
								setIsLoading(false);
							});
					} else {
						setCampaignErrors([
							__('Failed to fetch campaign', 'kudos-donations'),
						]);
						setIsLoading(false);
					}
				});
		}
	}, [campaignId]);

	const contextValue = useMemo(
		() => ({
			campaign,
			campaignErrors,
			isLoading,
		}),
		[campaign, campaignErrors, isLoading]
	);

	return (
		<CampaignContext.Provider value={contextValue}>
			{isLoading ? (
				<Flex justify="center">
					<Spinner />
				</Flex>
			) : (
				children
			)}
		</CampaignContext.Provider>
	);
}

export const useCampaignContext = () => useContext(CampaignContext);
