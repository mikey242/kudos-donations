import React from 'react';
import { useCallback, useEffect, useState } from '@wordpress/element';
import CampaignEdit from './CampaignEdit';
import { CampaignsTable } from './CampaignsTable';
import { usePostsContext, useAdminContext } from '../contexts';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import GenerateShortcode from './GenerateShortcode';
import type { Campaign } from '../../../types/posts';

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

const NewCampaignButton = ({ handleClick }) => (
	<Button
		variant="primary"
		onClick={handleClick}
		text={__('New campaign', 'kudos-donations')}
		icon="plus"
	/>
);

export const CampaignsPage = (): React.ReactNode => {
	const [currentCampaign, setCurrentCampaign] = useState<Campaign | null>(
		null
	);
	const { posts, handleNew } = usePostsContext<Campaign>();
	const { setHeaderContent, searchParams, setQueryParams } =
		useAdminContext();
	const campaignId = searchParams.get('edit');

	const clearCurrentCampaign = useCallback(() => {
		setCurrentCampaign(null);
		setQueryParams({
			delete: ['edit', 'order', 'tab'],
		});
	}, [setQueryParams]);

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
		} else {
			setHeaderContent(
				<NewCampaignButton
					handleClick={(
						e: React.SyntheticEvent | Partial<Campaign>
					) => {
						handleNew(e).then((response: Campaign) => {
							if (response?.id) {
								setQueryParams({
									set: [
										{
											name: 'edit',
											value: String(response.id),
										},
									],
								});
							}
						});
					}}
				/>
			);
		}
		return () => setHeaderContent(null);
	}, [
		clearCurrentCampaign,
		currentCampaign,
		handleNew,
		setHeaderContent,
		setQueryParams,
	]);

	return (
		<>
			{campaignId ? (
				currentCampaign && (
					<div className="admin-wrap">
						<CampaignEdit campaign={currentCampaign} />
					</div>
				)
			) : (
				<CampaignsTable
					handleEdit={(name: string, value: string) => {
						setQueryParams({
							set: [{ name, value }],
						});
					}}
				/>
			)}
		</>
	);
};
