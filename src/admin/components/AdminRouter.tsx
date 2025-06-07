import React from 'react';
import { PostsProvider } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import { DonorsTable } from './donors';
import { TransactionsTable } from './transactions';
import { SubscriptionsTable } from './subscriptions';
import { SettingsPage } from './settings';
import { EntityPage } from './EntityPage';
import { CampaignsTable } from './campaigns';
import CampaignEdit from './campaigns/CampaignEdit';
import type { Campaign } from '../../types/posts';
import SinglePostView from './SinglePostView';
import { Flex } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const AdminPages = {
	'kudos-campaigns': () => (
		<PostsProvider
			pluralName={__('Campaigns', 'kudos-donations')}
			singularName={__('Campaign', 'kudos-donations')}
			postType={'kudos_campaign'}
		>
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
		<PostsProvider
			pluralName={__('Donors', 'kudos-donations')}
			singularName={__('Donor', 'kudos-donations')}
			postType="kudos_donor"
		>
			<EntityPage
				renderTable={(editPost) => (
					<DonorsTable handleEdit={editPost} />
				)}
				renderEdit={(post) => <SinglePostView post={post} />}
			/>
		</PostsProvider>
	),
	'kudos-transactions': () => (
		<PostsProvider
			pluralName={__('Transactions', 'kudos-donations')}
			singularName={__('Transaction', 'kudos-donations')}
			postType="kudos_transaction"
		>
			<EntityPage
				renderTable={(editPost) => (
					<TransactionsTable handleEdit={editPost} />
				)}
				renderEdit={(post) => <SinglePostView post={post} />}
			/>
		</PostsProvider>
	),
	'kudos-subscriptions': () => (
		<PostsProvider
			pluralName={__('Subscriptions', 'kudos-donations')}
			singularName={__('Subscription', 'kudos-donations')}
			postType="kudos_subscription"
		>
			<EntityPage
				renderTable={(editPost) => (
					<SubscriptionsTable handleEdit={editPost} />
				)}
				renderEdit={(post) => <SinglePostView post={post} />}
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
