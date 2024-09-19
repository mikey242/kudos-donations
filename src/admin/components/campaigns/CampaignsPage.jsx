import { AdminHeader } from '../AdminHeader';
import React from 'react';
import { useEffect, useState } from '@wordpress/element';
import CampaignEdit from './CampaignEdit';
import { CampaignsTable } from './CampaignsTable';
import { useCampaignsContext } from '../../contexts/CampaignsContext';
import { useSearchParams } from 'react-router-dom';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	Button,
	Flex,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import GenerateShortcode from './GenerateShortcode';
import { useAdminContext } from '../../contexts/AdminContext';

export const CampaignsPage = () => {
	const [searchParams, setSearchParams] = useSearchParams();
	const [currentCampaign, setCurrentCampaign] = useState(null);
	const campaignId = searchParams.get('edit');
	const { posts, hasResolved } = useCampaignsContext();
	const { setHeaderContent } = useAdminContext();

	const updateParam = (name, value) => {
		searchParams.set(name, value);
		setSearchParams(searchParams);
	};

	const clearCurrentCampaign = () => {
		setCurrentCampaign(null);
		searchParams.delete('edit');
		searchParams.delete('order');
		setSearchParams(searchParams);
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
			searchParams.set('edit', currentCampaign.id);
			setSearchParams(searchParams);
			setHeaderContent(<NavigationButtons />);
		}
		return () => setHeaderContent(null);
	}, [currentCampaign, searchParams, setHeaderContent, setSearchParams]);

	return (
		<>
			<AdminHeader />
			<div className="admin-wrap">
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
			</div>
		</>
	);
};
