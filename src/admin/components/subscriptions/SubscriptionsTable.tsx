import {
	Button,
	Dashicon,
	Flex,
	Tooltip,
	VisuallyHidden,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Table } from '../table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import type { Subscription } from '../../../types/entity';
import { IconKey } from '@wordpress/components/build-types/dashicon/types';
import { useEntitiesContext, useSettingsContext } from '../../contexts';
import { useAdminQueryParams } from '../../hooks';
export const SubscriptionsTable = ({ handleEdit }): React.ReactNode => {
	const { currencies } = window.kudos;
	const { setParams } = useAdminQueryParams();
	const { settings } = useSettingsContext();
	const { handleDelete } = useEntitiesContext();

	const changeView = (entityId: number) => {
		void setParams({
			page: 'kudos-transactions',
			where: { subscription_id: String(entityId) },
		});
	};

	const headerItems = [
		{
			key: 'donor',
			title: __('Donor', 'kudos-donations'),
			valueCallback: (post: Subscription): React.ReactNode =>
				post.donor?.name ?? post.donor?.email ?? '',
		},
		{
			key: 'status',
			title: __('Status', 'kudos-donations'),
			orderby: 'status',
			valueCallback: (post: Subscription): React.ReactNode => {
				const status = post?.status;

				const statusConfig: Record<
					string,
					{ title: string; icon: string }
				> = {
					active: {
						title: __('Active', 'kudos-donations'),
						icon: 'yes-alt',
					},
					cancelled: {
						title: __('Canceled', 'kudos-donations'),
						icon: 'no-alt',
					},
					suspended: {
						title: __('Suspended', 'kudos-donations'),
						icon: 'warning',
					},
					completed: {
						title: __('Completed', 'kudos-donations'),
						icon: 'yes',
					},
				};
				const config = statusConfig[status];

				return (
					config && (
						<Dashicon
							title={config.title}
							icon={config.icon as IconKey}
						/>
					)
				);
			},
		},
		{
			key: 'value',
			title: __('Amount', 'kudos-donations'),
			orderby: 'value',
			valueCallback: (post: Subscription): React.ReactNode => {
				const value = post?.value;
				const currency = post?.currency;

				if (!value || !currency) {
					return null;
				}

				const currencySymbol = currencies[post?.currency] ?? currency;
				return (
					<span>
						{currencySymbol}
						{value}
					</span>
				);
			},
		},
		{
			key: 'campaign',
			title: __('Campaign', 'kudos-donations'),
			valueCallback: (post: Subscription): React.ReactNode =>
				post.campaign?.title,
		},
		{
			key: 'frequency',
			title: __('Frequency', 'kudos-donations'),
			valueCallback: (post: Subscription): React.ReactNode =>
				post.frequency,
		},
		{
			key: 'length',
			title: __('Length', 'kudos-donations'),
			valueCallback: (post: Subscription): React.ReactNode => post.years,
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'created_at',
			valueCallback: (post: Subscription): React.ReactNode => (
				<Tooltip text={dateI18n('d-m-Y H:i:s', post.created_at, null)}>
					<i>{dateI18n('d-m-Y', post.created_at, null)}</i>
				</Tooltip>
			),
		},
		{
			key: 'edit',
			title: (
				<VisuallyHidden>{__('Edit', 'kudos-donations')}</VisuallyHidden>
			),
			valueCallback: (post: Subscription): React.ReactNode => (
				<Flex justify="flex-end">
					<Button
						size="compact"
						icon="money-alt"
						disabled={!post.donor}
						onClick={() => changeView(post.id)}
						title={__('View donations', 'kudos-donations')}
					/>
					{settings._kudos_debug_mode && (
						<Button
							size="compact"
							icon="media-document"
							onClick={() => handleEdit(post.id)}
							title={__('View more', 'kudos-donations')}
						/>
					)}
					<Button
						size="compact"
						icon="trash"
						label={__('Delete donor', 'kudos-donations')}
						onClick={() => {
							return (
								// eslint-disable-next-line no-alert
								window.confirm(
									__(
										'Are you sure you wish to delete this donor?',
										'kudos-donations'
									)
								) && handleDelete(post.id)
							);
						}}
					/>
				</Flex>
			),
		},
	];

	const filters = [
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

	return (
		<>
			<Table filters={filters} headerItems={headerItems} />
		</>
	);
};
