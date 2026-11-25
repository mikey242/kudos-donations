import { Flex, Icon } from '@wordpress/components';
import type { IconType } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { getPaymentVendor } from '../../../utils/payment-vendor';
import type { Transaction } from '../../../types/entity';
import {
	createdAtColumn,
	DetailsModal,
	PaymentVendorIcon,
	StatusIcon,
	Table,
} from '../../components';
import type {
	Column,
	Filter,
	RowActionList,
	StatusConfig,
} from '../../components';
import { useRowActions } from '../use-row-actions';
import { formatAmount } from '../../../utils/currency';
import {
	arrowRight as arrowRightIcon,
	cancelCircleFilled as cancelCircleFilledIcon,
	caution as cautionIcon,
	download as downloadIcon,
	page as pageIcon,
	published as publishedIcon,
	repeat as repeatIcon,
	scheduled as scheduledIcon,
} from '@wordpress/icons';

// Keys match PaymentStatus in includes/Enum/PaymentStatus.php.
const statusConfig: Record<string, StatusConfig> = {
	paid: { title: __('Paid', 'kudos-donations'), icon: publishedIcon },
	open: { title: __('Open', 'kudos-donations'), icon: scheduledIcon },
	failed: { title: __('Failed', 'kudos-donations'), icon: cautionIcon },
	canceled: {
		title: __('Canceled', 'kudos-donations'),
		icon: cancelCircleFilledIcon,
	},
	expired: { title: __('Expired', 'kudos-donations'), icon: cautionIcon },
};

const filters: Filter[] = Object.entries(statusConfig).map(
	([status, { title }]) => ({ label: title, where: { status } })
);

const sequenceIcons: Record<string, IconType> = {
	oneoff: arrowRightIcon,
	first: repeatIcon,
	recurring: repeatIcon,
};

export const TransactionsTable = (): React.ReactNode => {
	const rowActions = useRowActions<Transaction>();

	const columns: Column<Transaction>[] = [
		{
			key: 'donor',
			title: __('Donor', 'kudos-donations'),
			render: (transaction) =>
				transaction.donor?.name || transaction.donor?.email || '',
		},
		{
			key: 'status',
			title: __('Status', 'kudos-donations'),
			size: 'small',
			orderby: 'status',
			render: (transaction) => (
				<StatusIcon status={transaction.status} config={statusConfig} />
			),
		},
		{
			key: 'value',
			title: __('Amount', 'kudos-donations'),
			size: 'medium',
			orderby: 'value',
			render: ({ value, currency, sequence_type: sequence }) => {
				if (!value || !currency) {
					return null;
				}
				const icon = sequenceIcons[sequence];
				return (
					<Flex
						justify="flex-start"
						title={
							__('Payment type:', 'kudos-donations') +
							' ' +
							sequence
						}
					>
						{icon && <Icon icon={icon} />}
						{formatAmount(value, currency)}
					</Flex>
				);
			},
		},
		{
			key: 'campaign',
			title: __('Campaign', 'kudos-donations'),
			render: (transaction) => transaction.campaign?.title ?? '',
		},
		{
			key: 'vendor',
			title: __('Provider', 'kudos-donations'),
			size: 'small',
			render: (transaction) => (
				<PaymentVendorIcon
					title={transaction.vendor}
					icon={getPaymentVendor(transaction.vendor)?.icon}
				/>
			),
		},
		createdAtColumn(),
	];

	const actions: RowActionList<Transaction> = [
		rowActions.inspect(),
		{
			id: 'message',
			label: __('View message', 'kudos-donations'),
			icon: pageIcon,
			isEligible: (transaction) => Boolean(transaction.message),
			render: (transaction) => (
				<DetailsModal
					title={__('Message', 'kudos-donations')}
					label={__('View message', 'kudos-donations')}
					icon={pageIcon}
					content={
						<p style={{ fontSize: '16px' }}>
							{transaction.message}
						</p>
					}
				/>
			),
		},
		{
			id: 'receipt',
			label: __('View receipt', 'kudos-donations'),
			icon: downloadIcon,
			href: (transaction) => transaction.receipt_url,
			isDisabled: (transaction) => transaction.status !== 'paid',
		},
		rowActions.remove(
			__('Delete transaction', 'kudos-donations'),
			__(
				'Are you sure you wish to delete this transaction?',
				'kudos-donations'
			)
		),
	];

	return <Table columns={columns} actions={actions} filters={filters} />;
};
