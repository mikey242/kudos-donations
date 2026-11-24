import {
	Button,
	Flex,
	Icon,
	Tooltip,
	VisuallyHidden,
} from '@wordpress/components';
import type { IconType } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { useEntitiesContext, useSettingsContext } from '../../contexts';
import { useAdminQueryParams } from '../../hooks';
import { confirmDelete } from '../../../utils';
import { getPaymentVendor } from '../../../utils/payment-vendor';
import type { Campaign, Transaction } from '../../../types/entity';
import {
	DetailsModal,
	HeaderItem,
	PaymentVendorIcon,
	StatusIcon,
	Table,
} from '../../components';
import type { StatusConfig } from '../../components';
import { getCurrencySymbol } from '../../../utils/currency';
import {
	arrowRight as arrowRightIcon,
	cancelCircleFilled as cancelCircleFilledIcon,
	caution as cautionIcon,
	download as downloadIcon,
	page as pageIcon,
	pencil as pencilIcon,
	published as publishedIcon,
	repeat as repeatIcon,
	scheduled as scheduledIcon,
	trash as trashIcon,
} from '@wordpress/icons';

const transactionStatusConfig: Record<string, StatusConfig> = {
	paid: { title: __('Paid', 'kudos-donations'), icon: publishedIcon },
	open: { title: __('Open', 'kudos-donations'), icon: scheduledIcon },
	cancelled: {
		title: __('Canceled', 'kudos-donations'),
		icon: cancelCircleFilledIcon,
	},
	expired: { title: __('Expired', 'kudos-donations'), icon: cautionIcon },
	failed: { title: __('Failed', 'kudos-donations'), icon: cautionIcon },
};

export const TransactionsTable = (): React.ReactNode => {
	const { settings } = useSettingsContext();
	const { handleDelete } = useEntitiesContext();
	const { updateParams } = useAdminQueryParams();

	const handleEdit = (id: number) => void updateParams({ entity: id });

	const headerItems: HeaderItem[] = [
		{
			key: 'donor',
			title: __('Donor', 'kudos-donations'),
			valueCallback: (post: Transaction): React.ReactNode =>
				post?.donor?.name || post?.donor?.email || '',
		},
		{
			key: 'status',
			title: __('Status', 'kudos-donations'),
			orderby: 'status',
			valueCallback: (post: Transaction): React.ReactNode => (
				<StatusIcon
					status={post.status}
					config={transactionStatusConfig}
				/>
			),
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

				const currencySymbol =
					getCurrencySymbol(post.currency) ?? currency;

				let icon: IconType | null;

				switch (sequence) {
					case 'oneoff':
						icon = arrowRightIcon;
						break;

					case 'recurring':
					case 'first':
						icon = repeatIcon;
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
						{icon && <Icon icon={icon} />}
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
			key: 'provider',
			title: __('Provider', 'kudos-donations'),
			align: 'left',
			valueCallback: (post: Transaction): React.ReactNode => (
				<PaymentVendorIcon
					title={post.vendor}
					icon={getPaymentVendor(post.vendor)?.icon}
				/>
			),
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'created_at',
			valueCallback: (post: Campaign): React.ReactNode => (
				<Tooltip text={dateI18n('d-m-Y H:i:s', post.created_at)}>
					<i>{dateI18n('d-m-Y', post.created_at)}</i>
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
				const url = post.receipt_url;
				return (
					<>
						{settings._kudos_debug_mode && (
							<Button
								size="compact"
								icon={pencilIcon}
								onClick={() => handleEdit(post.id)}
								title={__('View more', 'kudos-donations')}
							/>
						)}
						{post.message && (
							<DetailsModal
								title={__('Message', 'kudos-donations')}
								label={__('View message', 'kudos-donations')}
								icon={pageIcon}
								content={
									<p style={{ fontSize: '16px' }}>
										{post.message}
									</p>
								}
							/>
						)}
						<Button
							size="compact"
							icon={downloadIcon}
							disabled={status !== 'paid'}
							href={url}
							title={__('View receipt', 'kudos-donations')}
						/>
						<Button
							size="compact"
							icon={trashIcon}
							label={__('Delete transaction', 'kudos-donations')}
							onClick={() =>
								confirmDelete(
									__(
										'Are you sure you wish to delete this transaction?',
										'kudos-donations'
									),
									() => handleDelete(post.id)
								)
							}
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

	return <Table filters={filters} headerItems={headerItems} />;
};
