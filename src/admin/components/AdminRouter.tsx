import { Flex, IconType } from '@wordpress/components';
import type { ReactNode } from 'react';
import { useAdminQueryParams } from '../hooks';
import { EntitiesProvider } from '../contexts';
import { CampaignsTable } from './campaigns';
import CampaignEdit from './campaigns/CampaignEdit';
import { EntityPage } from './EntityPage';
import { TransactionsTable } from './transactions';
import { TransactionEdit } from './transactions/TransactionEdit';
import { __ } from '@wordpress/i18n';
import { SubscriptionsTable } from './subscriptions';
import { DonorsTable } from './donors';
import { SettingsPage } from './settings';
import { SingleEntityEdit } from './SingleEntityEdit';
import { Campaign } from '../../types/entity';
import { applyFilters } from '@wordpress/hooks';

export interface PageConfig {
	label: string;
	view: string;
	icon?: IconType;
	component: () => ReactNode;
}

export const AdminPages: PageConfig[] = applyFilters('kudosAdminPages', [
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
					renderEdit={(entity) =>
						entity && (
							<SingleEntityEdit
								data={entity}
								fields={[
									{
										id: 'title',
										label: 'Title',
										type: 'text',
									},
									{
										id: 'value',
										label: 'Value',
										type: 'text',
									},
									{
										id: 'currency',
										label: 'Currency',
										type: 'text',
									},
									{
										id: 'status',
										label: 'Status',
										type: 'text',
									},
									{
										id: 'frequency',
										label: 'Frequency',
										type: 'text',
									},
									{
										id: 'years',
										label: 'Years',
										type: 'integer',
									},
									{
										id: 'transaction_id',
										label: 'Transaction id',
										type: 'text',
									},
									{
										id: 'vendor_customer_id',
										label: 'Vendor customer id',
										type: 'text',
									},
									{
										id: 'vendor_subscription_id',
										label: 'Vendor subscription id',
										type: 'text',
									},
									{
										id: 'campaign_id',
										label: 'Campaign id',
										type: 'integer',
									},
									{
										id: 'donor_id',
										label: 'Donor id',
										type: 'integer',
									},
									{
										id: 'created_at',
										label: 'Created at',
										type: 'datetime',
									},
								]}
							/>
						)
					}
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
					renderEdit={(entity) =>
						entity && (
							<SingleEntityEdit
								data={entity}
								fields={[
									{
										id: 'title',
										label: 'Title',
										type: 'text',
									},
									{ id: 'name', label: 'Name', type: 'text' },
									{
										id: 'business_name',
										label: 'Business name',
										type: 'text',
									},
									{
										id: 'street',
										label: 'Street',
										type: 'text',
									},
									{ id: 'city', label: 'City', type: 'text' },
									{
										id: 'postcode',
										label: 'Postcode',
										type: 'text',
									},
									{
										id: 'country',
										label: 'Country',
										type: 'text',
									},
									{
										id: 'vendor_customer_id',
										label: 'Vendor customer id',
										type: 'text',
									},
									{
										id: 'locale',
										label: 'Locale',
										type: 'text',
									},
									{
										id: 'created_at',
										label: 'Created at',
										type: 'datetime',
									},
								]}
							/>
						)
					}
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
]) as PageConfig[];

export const AdminRouter = (): ReactNode => {
	const { params } = useAdminQueryParams();
	const page = params.page || AdminPages[0].view;

	const pageConfig = AdminPages.find((p) => p.view === page);

	if (!pageConfig) {
		return (
			<Flex justify="center">
				<p>{`Unknown view: "${page}"`}</p>
			</Flex>
		);
	}

	const CurrentPageComponent = pageConfig.component;

	// Force remount on view change.
	return <CurrentPageComponent key={page} />;
};
