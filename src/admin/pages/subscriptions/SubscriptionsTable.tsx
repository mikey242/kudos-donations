import { __ } from '@wordpress/i18n';
import React from 'react';
import apiFetch from '@wordpress/api-fetch';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import {
	notAllowed as notAllowedIcon,
	caution as cautionIcon,
	check as checkIcon,
	closeSmall as closeSmallIcon,
	published as publishedIcon,
} from '@wordpress/icons';
import { createdAtColumn, StatusIcon, Table } from '../../components';
import type {
	Column,
	Filter,
	RowActionList,
	StatusConfig,
} from '../../components';
import { useEntitiesContext } from '../../contexts';
import type { Subscription } from '../../../types/entity';
import { useRowActions } from '../use-row-actions';
import { formatAmount } from '../../../utils/currency';
import { getFrequencyName } from '../../../utils/frequency';

const statusConfig: Record<string, StatusConfig> = {
	active: { title: __('Active', 'kudos-donations'), icon: publishedIcon },
	cancelled: {
		title: __('Canceled', 'kudos-donations'),
		icon: notAllowedIcon,
	},
	suspended: { title: __('Suspended', 'kudos-donations'), icon: cautionIcon },
	completed: { title: __('Completed', 'kudos-donations'), icon: checkIcon },
};

const filters: Filter[] = [
	{
		label: __('Monthly', 'kudos-donations'),
		where: { frequency: '1 month' },
	},
	{
		label: __('Quarterly', 'kudos-donations'),
		where: { frequency: '3 months' },
	},
	{
		label: __('Yearly', 'kudos-donations'),
		where: { frequency: '12 months' },
	},
];

export const SubscriptionsTable = (): React.ReactNode => {
	const rowActions = useRowActions<Subscription>();
	const { fetchEntities } = useEntitiesContext<Subscription>();
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);

	const cancelSubscription = async (subscription: Subscription) => {
		try {
			const response: { message: string } = await apiFetch({
				path: `/kudos/v1/subscription/cancel/`,
				method: 'POST',
				data: { id: subscription.id, token: subscription.token },
			});
			void createSuccessNotice(response.message, {
				type: 'snackbar',
				icon: '✅',
			});
			fetchEntities();
		} catch (error: any) {
			void createErrorNotice(error.message, { type: 'snackbar' });
		}
	};

	const columns: Column<Subscription>[] = [
		{
			key: 'donor',
			title: __('Donor', 'kudos-donations'),
			render: (subscription) =>
				subscription.donor?.name || subscription.donor?.email || '',
		},
		{
			key: 'status',
			title: __('Status', 'kudos-donations'),
			size: 'small',
			orderby: 'status',
			render: (subscription) => (
				<StatusIcon
					status={subscription.status}
					config={statusConfig}
				/>
			),
		},
		{
			key: 'value',
			title: __('Amount', 'kudos-donations'),
			size: 'small',
			orderby: 'value',
			render: ({ value, currency }) =>
				value && currency ? formatAmount(value, currency) : null,
		},
		{
			key: 'campaign',
			title: __('Campaign', 'kudos-donations'),
			render: (subscription) => subscription.campaign?.title ?? '',
		},
		{
			key: 'frequency',
			title: __('Frequency', 'kudos-donations'),
			render: (subscription) => getFrequencyName(subscription.frequency),
		},
		{
			key: 'years',
			title: __('Length', 'kudos-donations'),
			render: (subscription) =>
				// Mollie stores 0 for a continuous subscription, Stripe stores null.
				!subscription.years
					? __('Continuous', 'kudos-donations')
					: subscription.years + ' ' + __('years', 'kudos-donations'),
		},
		createdAtColumn(),
	];

	const actions: RowActionList<Subscription> = [
		{
			...rowActions.viewTransactions('subscription_id'),
			isDisabled: (subscription) => !subscription.donor,
		},
		{
			id: 'cancel',
			label: __('Cancel subscription', 'kudos-donations'),
			icon: closeSmallIcon,
			isDisabled: (subscription) => subscription.status !== 'active',
			onClick: (subscription) => void cancelSubscription(subscription),
		},
		rowActions.inspect(),
		rowActions.remove(
			__('Delete subscription', 'kudos-donations'),
			__(
				'Are you sure you wish to delete this subscription?',
				'kudos-donations'
			)
		),
	];

	return <Table columns={columns} actions={actions} filters={filters} />;
};
