import { Button, Dashicon, Flex, VisuallyHidden } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { HeaderItem, Table } from '../table/Table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { useAdminContext, usePostsContext } from '../contexts';
import { useEffect } from '@wordpress/element';
import type { Transaction } from '../../../types/posts';
import {
	ArrowPathIcon,
	ArrowRightCircleIcon,
} from '@heroicons/react/24/outline';
import { IconKey } from '@wordpress/components/build-types/dashicon/types';
import { DetailsModal } from '../table/DetailsModal';
export const TransactionsTable = ({ handleEdit }): React.ReactNode => {
	const { currencies } = window.kudos;
	const { setPageTitle } = useAdminContext();
	const {
		handleDelete,
		isLoading,
		hasLoadedOnce,
		posts,
		totalPages,
		totalItems,
	} = usePostsContext();

	useEffect(() => {
		setPageTitle(__('Your transactions', 'kudos-donations'));
	}, [setPageTitle]);

	const headerItems: HeaderItem[] = [
		{
			key: 'donor',
			title: __('Donor', 'kudos-donations'),

			valueCallback: (post: Transaction): React.ReactNode =>
				post.donor?.meta?.name ?? post.donor?.meta.email ?? '',
		},
		{
			key: 'status',
			title: __('Status', 'kudos-donations'),
			width: '7%',
			valueCallback: (post: Transaction): React.ReactNode => {
				const status = post.meta.status;

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
					canceled: {
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
			orderby: 'meta_value_num',
			title: __('Amount', 'kudos-donations'),
			valueCallback: (post: Transaction): React.ReactNode => {
				const value = post.meta?.value;
				const currency = post.meta?.currency;
				const sequence = post.meta?.sequence_type;

				if (!value || !currency) {
					return null;
				}

				const currencySymbol =
					currencies[post.meta?.currency] ?? currency;

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
							__('Payment type:', 'kudos-donations') + sequence
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
				post.campaign?.title.raw ?? '',
		},
		{
			key: 'message',
			title: __('Message', 'kudos-donations'),
			align: 'center',
			width: '7%',
			valueCallback: (post: Transaction): React.ReactNode =>
				post.meta.message && (
					<DetailsModal
						title={__('Message', 'kudos-donations')}
						icon="text"
						content={
							<p style={{ fontSize: '16px' }}>
								{post.meta.message}
							</p>
						}
					/>
				),
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'date',
			width: '10%',
			valueCallback: (post: Transaction): React.ReactNode => (
				<i>{dateI18n('d-m-Y', post.date, null)}</i>
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
				const status = post.meta.status;
				const url = post.invoice_url;
				return (
					<>
						<Button
							size="compact"
							icon="media-document"
							onClick={() => handleEdit(post.id)}
							title={__('View more', 'kudos-donations')}
						/>
						{status === 'paid' && (
							<Button
								size="compact"
								icon="download"
								href={url}
								title={__('View invoice', 'kudos-donations')}
							/>
						)}
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

	return (
		<>
			<Table
				posts={posts}
				headerItems={headerItems}
				isLoading={isLoading}
				hasLoadedOnce={hasLoadedOnce}
				totalItems={totalItems}
				totalPages={totalPages}
			/>
		</>
	);
};
