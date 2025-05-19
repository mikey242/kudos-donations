import { Button, Dashicon, Flex, VisuallyHidden } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Table } from '../Table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { useAdminContext, usePostsContext } from '../contexts';
import { useEffect } from '@wordpress/element';
import type { Transaction } from '../../../types/wp';
import {
	ArrowPathIcon,
	ArrowRightCircleIcon,
} from '@heroicons/react/24/outline';
export const TransactionsTable = (): React.ReactNode => {
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

	const headerItems = [
		{
			key: 'description',
			title: __('Description', 'kudos-donations'),
			orderby: 'title',
			width: '20%',
			valueCallback: (post: Transaction): React.ReactNode =>
				post.title.raw,
		},
		{
			key: 'donor',
			title: __('Donor', 'kudos-donations'),
			width: '15%',
			valueCallback: (post: Transaction): React.ReactNode =>
				post.donor?.meta.email ??
				post.donor?.meta?.name ??
				post.donor?.id ??
				'',
		},
		{
			key: 'value',
			orderby: 'meta_value_num',
			title: __('Amount', 'kudos-donations'),
			width: '5%',
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
						justify="center"
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
			width: '20%',
			valueCallback: (post: Transaction): React.ReactNode =>
				post.campaign?.title.raw ?? '',
		},
		{
			key: 'status',
			orderby: 'meta_value',
			title: __('Status', 'kudos-donations'),
			width: '5%',
			valueCallback: (post: Transaction): React.ReactNode => {
				const status = post.meta.status;

				switch (status) {
					case 'paid':
						return (
							<Flex>
								<Dashicon
									title={__('Paid', 'kudos-donations')}
									icon="yes-alt"
								/>
							</Flex>
						);

					case 'open':
						return (
							<Flex>
								<Dashicon
									title={__('Open', 'kudos-donations')}
									icon="clock"
								/>
							</Flex>
						);

					case 'canceled':
						return (
							<Flex>
								<Dashicon
									title={__('Canceled', 'kudos-donations')}
									icon="no-alt"
								/>
							</Flex>
						);

					case 'expired':
						return (
							<Flex>
								<Dashicon
									title={__('Expired', 'kudos-donations')}
									icon="warning"
								/>
							</Flex>
						);

					case 'failed':
						return (
							<Flex>
								<Dashicon
									title={__('Failed', 'kudos-donations')}
									icon="warning"
								/>
							</Flex>
						);

					default:
						return status;
				}
			},
		},
		{
			key: 'message',
			title: __('Message', 'kudos-donations'),
			width: '20%',
			valueCallback: (post: Transaction): string => post.meta.message,
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
			width: '5%',
			valueCallback: (post: Transaction): React.ReactNode => {
				const status = post.meta.status;
				const url = post.invoice_url;
				return (
					<Flex justify="flex-end">
						{status === 'paid' && (
							<Button
								size="compact"
								icon="media-document"
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
					</Flex>
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
