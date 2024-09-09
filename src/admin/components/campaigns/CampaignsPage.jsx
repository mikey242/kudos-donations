// eslint-disable-next-line import/default
import { AdminHeader } from '../AdminHeader';
import React from 'react';
import { useEffect, useState } from '@wordpress/element';
import CampaignEdit from './CampaignEdit';
import { CampaignsTable } from './CampaignsTable';
import { useCampaignsContext } from '../../contexts/CampaignsContext';
import { useSearchParams } from 'react-router-dom';

export const CampaignsPage = () => {
	const [searchParams, setSearchParams] = useSearchParams();
	const [currentCampaign, setCurrentCampaign] = useState(null);
	const campaignId = searchParams.get('edit');
	const { posts, hasResolved } = useCampaignsContext();

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
		}
	}, [currentCampaign, searchParams, setSearchParams]);

	return (
		<>
			<AdminHeader />
			<div className="admin-wrap">
				{campaignId ? (
					currentCampaign && (
						<CampaignEdit
							campaign={currentCampaign}
							recurringAllowed={false}
							handleGoBack={clearCurrentCampaign}
						/>
					)
				) : (
					<CampaignsTable handleEdit={updateParam} />
				)}
			</div>
		</>
	);
};
