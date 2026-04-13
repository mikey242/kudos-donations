/* eslint-disable camelcase */

import type { ReactNode } from 'react';
import { __ } from '@wordpress/i18n';
import {
	createContext,
	useContext,
	useEffect,
	useMemo,
	useState,
} from '@wordpress/element';
import type { Campaign } from '../../types/entity';

const fetchCampaign = async (path: string): Promise<Campaign> => {
	const root: string =
		(window as Window & { wpApiSettings?: { root?: string } })
			?.wpApiSettings?.root ?? '/wp-json/';
	const url = `${root.replace(/\/$/, '')}/kudos/v1/${path}`;
	const res = await window.fetch(url, { credentials: 'omit' });
	const data = await res.json();
	return res.ok ? data : Promise.reject(data);
};

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

export const CampaignProvider = ({
	campaignId,
	children,
}: CampaignProviderProps) => {
	const [campaign, setCampaign] = useState<Campaign | null>(null);
	const [campaignErrors, setCampaignErrors] = useState<string[]>(null);
	const [isLoading, setIsLoading] = useState<boolean>(true);
	/**
	 * Fetch campaign by ID first, fallback to slug if not found.
	 * Slug fetch is enabled only if the ID fetch resolves and finds nothing.
	 */
	useEffect(() => {
		if (campaignId) {
			setCampaignErrors(null);

			// Try by ID
			fetchCampaign(`campaign/${campaignId}`)
				.then((postById: Campaign) => {
					setCampaign(postById);
					setIsLoading(false);
				})
				.catch((error) => {
					if (error?.data?.status === 404) {
						// Try by wp_post_slug
						fetchCampaign(`campaign/by-slug/${campaignId}`)
							.then((postBySlug: Campaign) => {
								if (postBySlug) {
									setCampaign(postBySlug);
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
			{children}
		</CampaignContext.Provider>
	);
};

export const useCampaignContext = (): CampaignContextType => {
	const context = useContext(CampaignContext);
	if (!context) {
		throw new Error(
			'useCampaignContext must be used within a CampaignProvider'
		);
	}
	return context;
};
