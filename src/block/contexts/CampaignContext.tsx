/* eslint-disable camelcase */

import React, { createContext, ReactNode, useContext } from 'react';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Flex, Spinner } from '@wordpress/components';
import type { Campaign } from '../../types/wp';

interface CampaignContextType {
	campaign: Campaign | null;
	campaignErrors: string[] | null;
	isLoading: boolean;
}

interface CampaignProviderProps {
	campaignId: string;
	children: ReactNode;
}

export const CampaignContext = createContext<CampaignContextType>(null);

export default function CampaignProvider({
	campaignId,
	children,
}: CampaignProviderProps) {
	const [campaign, setCampaign] = useState<Campaign | null>(null);
	const [campaignErrors, setCampaignErrors] = useState<string[]>(null);
	const [isLoading, setIsLoading] = useState<boolean>(false);
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
				.then((postById: unknown) => {
					setCampaign(postById as Campaign);
					setIsLoading(false);
				})
				.catch((error) => {
					if (error?.data?.status === 404) {
						// Try by slug
						apiFetch({
							path: `/wp/v2/kudos_campaign?slug=${campaignId}`,
						})
							.then((postBySlug: unknown) => {
								const posts = postBySlug as Campaign[];
								if (posts.length > 0) {
									setCampaign(posts[0]);
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

	const contextValue: CampaignContextType = useMemo(
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

export const useCampaignContext = (): CampaignContextType => {
	const context = useContext(CampaignContext);
	if (!context) {
		throw new Error(
			'useCampaignContext must be used within a CampaignProvider'
		);
	}
	return context;
};
