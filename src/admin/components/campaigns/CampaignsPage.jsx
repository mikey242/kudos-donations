import React from 'react';
import { useEffect, useState } from '@wordpress/element';
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

export const CampaignsPage = () => {
	const [currentCampaign, setCurrentCampaign] = useState(null);
	const { posts, hasResolved } = useCampaignsContext();
	const { setHeaderContent, updateParam, searchParams, deleteParams } =
		useAdminContext();
	const campaignId = searchParams.get('edit');

	const clearCurrentCampaign = () => {
		setCurrentCampaign(null);
		deleteParams(['edit', 'order', 'tab']);
	};

	const NavigationButtons = () => (
		<>
			<Button
				variant="secondary"
				icon="arrow-left"
				onClick={() => clearCurrentCampaign()}
				type="button"
			>
				{__('Back', 'kudos-donations')}
			</Button>
			<GenerateShortcode campaign={currentCampaign} />
			<Button variant="primary" type="submit" form="campaign-form">
				{__('Save', 'kudos-donations')}
			</Button>
		</>
	);

	useEffect(() => {
		if (campaignId && posts) {
			setCurrentCampaign(
				posts.find((post) => post.id === Number(campaignId))
			);
		}
	}, [campaignId, hasResolved, posts]);

	useEffect(() => {
		if (currentCampaign) {
			setHeaderContent(<NavigationButtons />);
		}
		return () => setHeaderContent(null);
	}, [currentCampaign, setHeaderContent]);

	return (
		<>
			<>
				{campaignId ? (
					currentCampaign && (
						<>
							<CampaignEdit
								campaign={currentCampaign}
								recurringAllowed={false}
							/>
							<Spacer marginTop={'5'} />
							<Flex justify="flex-start">
								<NavigationButtons />
							</Flex>
						</>
					)
				) : (
					<CampaignsTable handleEdit={updateParam} />
				)}
			</>
		</>
	);
};
