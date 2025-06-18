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
import SingleEntityView from './SingleEntityView';
import { Flex } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';

const AdminPages = {
	'kudos-campaigns': () => (
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
	'kudos-donors': () => (
		<EntitiesProvider
			pluralName={__('Donors', 'kudos-donations')}
			singularName={__('Donor', 'kudos-donations')}
			entityType="donor"
		>
			<EntityPage
				renderTable={(editEntity) => (
					<DonorsTable handleEdit={editEntity} />
				)}
				renderEdit={(entity) => <SingleEntityView entity={entity} />}
			/>
		</EntitiesProvider>
	),
	'kudos-transactions': () => (
		<EntitiesProvider
			pluralName={__('Transactions', 'kudos-donations')}
			singularName={__('Transaction', 'kudos-donations')}
			entityType="transaction"
		>
			<EntityPage
				renderTable={(editEntity) => (
					<TransactionsTable handleEdit={editEntity} />
				)}
				renderEdit={(entity) => <SingleEntityView entity={entity} />}
			/>
		</EntitiesProvider>
	),
	'kudos-subscriptions': () => (
		<EntitiesProvider
			pluralName={__('Subscriptions', 'kudos-donations')}
			singularName={__('Subscription', 'kudos-donations')}
			entityType="subscription"
		>
			<EntityPage
				renderTable={(editEntity) => (
					<SubscriptionsTable handleEdit={editEntity} />
				)}
				renderEdit={(entity) => <SingleEntityView entity={entity} />}
			/>
		</EntitiesProvider>
	),
	'kudos-settings': () => <SettingsPage />,
};

export const AdminRouter = ({ defaultView }): React.ReactNode => {
	const { params } = useAdminQueryParams();
	const page = params.page ?? defaultView;

	const CurrentPageComponent = AdminPages[page];

	if (!CurrentPageComponent) {
		return (
			<Flex justify="center">
				<p>{`Unknown view: "${page}"`}</p>
			</Flex>
		);
	}

	// 🔑 Force remount on view change
	return <CurrentPageComponent key={page} />;
};
