import React from 'react';
import { PostsProvider, useAdminContext } from './contexts';
import { CampaignsPage } from './campaigns/CampaignsPage';
import { DonorsTable } from './donors/DonorsTable';
import { TransactionsTable } from './transactions/TransactionsTable';
import { SubscriptionsTable } from './subscriptions/SubscriptionsTable';
import { SettingsPage } from './settings/SettingsPage';
import { useEffect } from '@wordpress/element';

interface Props {
	defaultView: string;
}
export const AdminRouter = ({ defaultView }: Props): React.ReactNode => {
	const { searchParams, updateParam } = useAdminContext();
	const view = searchParams.get('view') ?? defaultView;

	useEffect(() => {
		updateParam('view', view);
	}, [updateParam, view]);

	switch (view) {
		case 'kudos-transactions':
			return (
				<PostsProvider postType="kudos_transaction">
					<TransactionsTable />
				</PostsProvider>
			);
		case 'kudos-subscriptions':
			return (
				<PostsProvider postType="kudos_subscription">
					<SubscriptionsTable />
				</PostsProvider>
			);
		case 'kudos-donors':
			return (
				<PostsProvider postType="kudos_donor">
					<DonorsTable />
				</PostsProvider>
			);
		case 'kudos-settings':
			return <SettingsPage />;
		case 'kudos-campaigns':
			return (
				<PostsProvider postType={'kudos_campaign'}>
					<CampaignsPage />
				</PostsProvider>
			);
		default:
			return (
				<div>
					<p>{`Unknown view: "${view}"`}</p>
				</div>
			);
	}
};
