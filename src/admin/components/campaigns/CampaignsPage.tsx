import React from 'react';
import { useCallback, useEffect, useState } from '@wordpress/element';
import CampaignEdit from './CampaignEdit';
import { CampaignsTable } from './CampaignsTable';
import { useCampaignsContext, useAdminContext } from '../contexts';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	Button,
	Flex,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import GenerateShortcode from './GenerateShortcode';
import type { Campaign } from '../../../types/wp';

const NavigationButtons = ({ campaign, onBack }): React.ReactNode => (
	<>
		<Button
			variant="secondary"
			icon="arrow-left"
			onClick={onBack}
			type="button"
		>
			{__('Back', 'kudos-donations')}
		</Button>
		<GenerateShortcode campaign={campaign} />
		<Button variant="primary" type="submit" form="campaign-form">
			{__('Save', 'kudos-donations')}
		</Button>
	</>
);

export const CampaignsPage = (): React.ReactNode => {
	const [currentCampaign, setCurrentCampaign] = useState<Campaign | null>(
		null
	);
	const { posts } = useCampaignsContext();
	const { setHeaderContent, updateParam, searchParams, deleteParams } =
		useAdminContext();
	const campaignId = searchParams.get('edit');

	const clearCurrentCampaign = useCallback(() => {
		setCurrentCampaign(null);
		deleteParams(['edit', 'order', 'tab']);
	}, [deleteParams]);

	useEffect(() => {
		if (campaignId && posts) {
			setCurrentCampaign(
				posts.find((post) => post.id === Number(campaignId))
			);
		}
	}, [campaignId, posts]);

	useEffect(() => {
		if (currentCampaign) {
			setHeaderContent(
				<NavigationButtons
					campaign={currentCampaign}
					onBack={clearCurrentCampaign}
				/>
			);
		}
		return () => setHeaderContent(null);
	}, [clearCurrentCampaign, currentCampaign, setHeaderContent]);

	return (
		<>
			{campaignId ? (
				currentCampaign && (
					<>
						<CampaignEdit campaign={currentCampaign} />
						<Spacer marginTop={'5'} />
						<Flex justify="flex-start">
							{
								<NavigationButtons
									campaign={currentCampaign}
									onBack={clearCurrentCampaign}
								/>
							}
						</Flex>
					</>
				)
			) : (
				<CampaignsTable handleEdit={updateParam} />
			)}
		</>
	);
};
