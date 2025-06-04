import React from 'react';
import { PostsProvider, useAdminQueryParams } from './contexts';
import { DonorsTable } from './donors/DonorsTable';
import { TransactionsTable } from './transactions/TransactionsTable';
import { SubscriptionsTable } from './subscriptions/SubscriptionsTable';
import { SettingsPage } from './settings/SettingsPage';
import { useEffect, useRef } from '@wordpress/element';
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

export const AdminRouter = ({
	defaultView,
}: {
	defaultView: string;
}): React.ReactNode => {
	const [params, setParams] = useAdminQueryParams();
	const { view } = params;
	const lastView = useRef<string | null>(null);

	useEffect(() => {
		// On first render, set the default view if not present
		if (!view) {
			void setParams({ view: defaultView });
			return;
		}

		// Skip clearing on first mount
		if (lastView.current === null) {
			lastView.current = view;
			return;
		}

		// If view changed, clear other stateful query params
		if (view !== lastView.current) {
			void setParams({
				post: null,
				order: null,
				tab: null,
				paged: 1,
			});
			lastView.current = view;
		}
	}, [view, defaultView, setParams]);

	const page = AdminPages[view];
	if (!page) {
		return (
			<Flex justify="center">
				<p>{`Unknown view: "${view}"`}</p>
			</Flex>
		);
	}

	return page();
};
