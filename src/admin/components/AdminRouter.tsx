import React from 'react';
import { EntitiesProvider } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import { DonorsTable } from './donors';
import { TransactionsTable } from './transactions';
import { SubscriptionsTable } from './subscriptions';
import { SettingsPage } from './settings';
import { EntityPage } from './EntityPage';
import { CampaignsTable } from './campaigns';
import CampaignEdit from './campaigns/CampaignEdit';
import type { Campaign } from '../../types/entity';
import { Flex, IconType } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { TransactionEdit } from './transactions/TransactionEdit';
import { SubscriptionEdit } from './subscriptions/SubscriptionEdit';
import { DonorEdit } from './donors/DonorEdit';

export interface PageConfig {
	label: string;
	view: string;
	icon?: IconType;
	component: () => React.ReactNode;
}

export const AdminPages: PageConfig[] = [
	{
		label: __('Campaigns', 'kudos-donations'),
		view: 'kudos-campaigns',
		icon: 'megaphone',
		component: () => (
			<EntitiesProvider
				pluralName={__('Campaigns', 'kudos-donations')}
				singularName={__('Campaign', 'kudos-donations')}
				entityType="campaign"
			>
				<EntityPage
					renderTable={(editEntity, newEntity) => (
						<CampaignsTable
							handleNew={newEntity}
							handleEdit={editEntity}
						/>
					)}
					renderEdit={(currentCampaign) => (
						<CampaignEdit campaign={currentCampaign as Campaign} />
					)}
				/>
			</EntitiesProvider>
		),
	},
	{
		label: __('Transactions', 'kudos-donations'),
		view: 'kudos-transactions',
		icon: 'money-alt',
		component: () => (
			<EntitiesProvider
				pluralName={__('Transactions', 'kudos-donations')}
				singularName={__('Transaction', 'kudos-donations')}
				entityType="transaction"
			>
				<EntityPage
					renderTable={(editEntity) => (
						<TransactionsTable handleEdit={editEntity} />
					)}
					renderEdit={(entity) => <TransactionEdit entity={entity} />}
				/>
			</EntitiesProvider>
		),
	},
	{
		label: __('Subscriptions', 'kudos-donations'),
		view: 'kudos-subscriptions',
		icon: 'update',
		component: () => (
			<EntitiesProvider
				pluralName={__('Subscriptions', 'kudos-donations')}
				singularName={__('Subscription', 'kudos-donations')}
				entityType="subscription"
			>
				<EntityPage
					renderTable={(editEntity) => (
						<SubscriptionsTable handleEdit={editEntity} />
					)}
					renderEdit={(entity) => (
						<SubscriptionEdit entity={entity} />
					)}
				/>
			</EntitiesProvider>
		),
	},
	{
		label: __('Donors', 'kudos-donations'),
		view: 'kudos-donors',
		icon: 'groups',
		component: () => (
			<EntitiesProvider
				pluralName={__('Donors', 'kudos-donations')}
				singularName={__('Donor', 'kudos-donations')}
				entityType="donor"
			>
				<EntityPage
					renderTable={(editEntity) => (
						<DonorsTable handleEdit={editEntity} />
					)}
					renderEdit={(entity) => <DonorEdit entity={entity} />}
				/>
			</EntitiesProvider>
		),
	},
	{
		label: __('Settings', 'kudos-donations'),
		view: 'kudos-settings',
		icon: 'admin-settings',
		component: () => <SettingsPage />,
	},
];

export const AdminRouter = (): React.ReactNode => {
	const { params } = useAdminQueryParams();
	const page = params.page;

	const CurrentPageComponent = AdminPages[page];

	if (!CurrentPageComponent) {
		return (
			<Flex justify="center">
				<p>{`Unknown view: "${page}"`}</p>
			</Flex>
		);
	}

	// Force remount on view change.
	return <CurrentPageComponent key={page} />;
};
