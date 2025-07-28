import {
	Button,
	Dashicon,
	Flex,
	Tooltip,
	VisuallyHidden,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { useEntitiesContext, useSettingsContext } from '../../contexts';
import type { Campaign, Transaction } from '../../../types/entity';
import {
	ArrowPathIcon,
	ArrowRightCircleIcon,
} from '@heroicons/react/24/outline';
import { IconKey } from '@wordpress/components/build-types/dashicon/types';
import { DetailsModal, HeaderItem, Table } from '../table';
export const TransactionsTable = ({ handleEdit }): React.ReactNode => {
	const { currencies } = window.kudos;
	const { settings } = useSettingsContext();
	const { handleDelete } = useEntitiesContext();

	const headerItems: HeaderItem[] = [
		{
			key: 'donor',
			title: __('Donor', 'kudos-donations'),

			valueCallback: (post: Transaction): React.ReactNode =>
				post.donor?.name ?? post.donor?.email ?? '',
		},
		{
			key: 'status',
			title: __('Status', 'kudos-donations'),
			orderby: 'status',
			valueCallback: (post: Transaction): React.ReactNode => {
				const status = post.status;

				const statusConfig: Record<
					string,
					{ title: string; icon: string }
				> = {
					paid: {
						title: __('Paid', 'kudos-donations'),
						icon: 'yes-alt',
					},
					open: {
						title: __('Open', 'kudos-donations'),
						icon: 'clock',
					},
					cancelled: {
						title: __('Canceled', 'kudos-donations'),
						icon: 'no-alt',
					},
					expired: {
						title: __('Expired', 'kudos-donations'),
						icon: 'warning',
					},
					failed: {
						title: __('Failed', 'kudos-donations'),
						icon: 'warning',
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
			valueCallback: (post: Transaction): React.ReactNode => {
				const value = post.value;
				const currency = post.currency;
				const sequence = post.sequence_type;

				if (!value || !currency) {
					return null;
				}

				const currencySymbol = currencies[post.currency] ?? currency;

				let icon: React.ReactNode;

				switch (sequence) {
					case 'oneoff':
						icon = <ArrowRightCircleIcon />;
						break;

					case 'recurring':
					case 'first':
						icon = <ArrowPathIcon />;
						break;

					default:
						icon = null;
				}

				return (
					<Flex
						justify="flex-start"
						title={
							__('Payment type:', 'kudos-donations') +
							' ' +
							sequence
						}
					>
						<div className="dashicons">{icon}</div>
						{currencySymbol}
						{value}
					</Flex>
				);
			},
		},
		{
			key: 'campaign',
			title: __('Campaign', 'kudos-donations'),
			valueCallback: (post: Transaction): React.ReactNode =>
				post.campaign?.title ?? '',
		},
		{
			key: 'message',
			title: __('Message', 'kudos-donations'),
			align: 'center',
			valueCallback: (post: Transaction): React.ReactNode =>
				post.message && (
					<DetailsModal
						title={__('Message', 'kudos-donations')}
						content={
							<p style={{ fontSize: '16px' }}>{post.message}</p>
						}
					/>
				),
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'created_at',
			valueCallback: (post: Campaign): React.ReactNode => (
				<Tooltip text={dateI18n('d-m-Y H:i:s', post.created_at, null)}>
					<i>{dateI18n('d-m-Y', post.created_at, null)}</i>
				</Tooltip>
			),
		},
		{
			key: 'actions',
			title: (
				<VisuallyHidden>
					{__('Actions', 'kudos-donations')}
				</VisuallyHidden>
			),
			align: 'right',
			valueCallback: (post: Transaction): React.ReactNode => {
				const status = post.status;
				const url = post.invoice_url;
				return (
					<>
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
							icon="download"
							disabled={status !== 'paid'}
							href={url}
							title={__('View invoice', 'kudos-donations')}
						/>
						<Button
							size="compact"
							icon="trash"
							label={__('Delete transaction', 'kudos-donations')}
							onClick={() => {
								return (
									// eslint-disable-next-line no-alert
									window.confirm(
										__(
											'Are you sure you wish to delete this transaction?',
											'kudos-donations'
										)
									) && handleDelete(post.id)
								);
							}}
						/>
					</>
				);
			},
		},
	];

	const filters = [
		{
			label: __('Paid', 'kudos-donations'),
			where: { status: 'paid' },
		},
		{
			label: __('Open', 'kudos-donations'),
			where: { status: 'open' },
		},
		{
			label: __('Failed', 'kudos-donations'),
			where: { status: 'failed' },
		},
		{
			label: __('Cancelled', 'kudos-donations'),
			where: { status: 'canceled' },
		},
		{
			label: __('Expired', 'kudos-donations'),
			where: { status: 'expired' },
		},
	];

	return (
		<>
			<Table filters={filters} headerItems={headerItems} />
		</>
	);
};
