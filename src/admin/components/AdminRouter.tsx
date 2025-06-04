import React from 'react';
import { PostsProvider } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import { DonorsTable } from './donors/DonorsTable';
import { TransactionsTable } from './transactions/TransactionsTable';
import { SubscriptionsTable } from './subscriptions/SubscriptionsTable';
import { SettingsPage } from './settings/SettingsPage';
import { EntityPage } from './EntityPage';
import { CampaignsTable } from './campaigns/CampaignsTable';
import CampaignEdit from './campaigns/CampaignEdit';
import type { Campaign } from '../../types/posts';
import DefaultEditView from './DefaultEditView';
import { Flex } from '@wordpress/components';

const AdminPages = {
	'kudos-campaigns': () => (
		<PostsProvider postType={'kudos_campaign'}>
			<EntityPage
				renderTable={(editPost, newPost) => (
					<CampaignsTable handleNew={newPost} handleEdit={editPost} />
				)}
				renderEdit={(currentCampaign) => (
					<CampaignEdit campaign={currentCampaign as Campaign} />
				)}
			/>
		</PostsProvider>
	),
	'kudos-donors': () => (
		<PostsProvider postType="kudos_donor">
			<EntityPage
				renderTable={(editPost) => (
					<DonorsTable handleEdit={editPost} />
				)}
				renderEdit={(post) => <DefaultEditView post={post} />}
			/>
		</PostsProvider>
	),
	'kudos-transactions': () => (
		<PostsProvider postType="kudos_transaction">
			<EntityPage
				renderTable={(editPost) => (
					<TransactionsTable handleEdit={editPost} />
				)}
				renderEdit={(post) => <DefaultEditView post={post} />}
			/>
		</PostsProvider>
	),
	'kudos-subscriptions': () => (
		<PostsProvider postType="kudos_subscription">
			<EntityPage
				renderTable={(editPost) => (
					<SubscriptionsTable handleEdit={editPost} />
				)}
				renderEdit={(post) => <DefaultEditView post={post} />}
			/>
		</PostsProvider>
	),
	'kudos-settings': () => <SettingsPage />,
};

export const AdminRouter = (): React.ReactNode => {
	const { params } = useAdminQueryParams();
	const { page } = params;

	const currentPage = AdminPages[page];
	if (!currentPage) {
		return (
			<Flex justify="center">
				<p>{`Unknown view: "${currentPage}"`}</p>
			</Flex>
		);
	}

	return currentPage();
};
