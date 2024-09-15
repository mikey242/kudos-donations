import React, { createContext, useContext } from 'react';
import { __ } from '@wordpress/i18n';
import { useEntityRecords } from '@wordpress/core-data';
import { Flex, Spinner } from '@wordpress/components';
import { useEffect, useMemo, useState } from '@wordpress/element';

export const CampaignContext = createContext(null);

export default function CampaignProvider({ campaignId, children }) {
	const [campaign, setCampaign] = useState(null);
	const [campaignErrors, setCampaignErrors] = useState(null);

	/**
	 * Fetch campaign by ID first, fallback to slug if not found.
	 * Slug fetch is enabled only if the ID fetch resolves and finds nothing.
	 */
	const { records: campaignsById, hasResolved: hasResolvedById } =
		useEntityRecords('postType', 'kudos_campaign', {
			include: [campaignId],
		});

	const { records: campaignsBySlug, hasResolved: hasResolvedBySlug } =
		useEntityRecords(
			'postType',
			'kudos_campaign',
			{ slug: campaignId },
			{
				enabled:
					hasResolvedById &&
					(!campaignsById || !campaignsById.length),
			}
		);

	useEffect(() => {
		if (hasResolvedById && campaignsById?.length) {
			setCampaign(campaignsById[0]);
			setCampaignErrors(null); // Clear errors if campaign found by ID
		} else if (hasResolvedBySlug && campaignsBySlug?.length) {
			setCampaign(campaignsBySlug[0]);
			setCampaignErrors(null); // Clear errors if campaign found by slug
		} else if (
			hasResolvedById &&
			hasResolvedBySlug &&
			!campaignsById?.length &&
			!campaignsBySlug?.length
		) {
			setCampaignErrors([__('Campaign not found')]);
		}
	}, [campaignsById, campaignsBySlug, hasResolvedById, hasResolvedBySlug]);

	const contextValue = useMemo(
		() => ({
			campaign,
			campaignErrors,
		}),
		[campaign, campaignErrors]
	);

	if (!campaign && !campaignErrors) {
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
