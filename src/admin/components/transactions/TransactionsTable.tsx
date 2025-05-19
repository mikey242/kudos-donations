import { Button, Flex, VisuallyHidden } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Table } from '../Table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { useAdminContext, usePostsContext } from '../contexts';
import { useEffect } from '@wordpress/element';
import type { Campaign, Transaction } from '../../../types/wp';
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
			valueCallback: (post: Transaction): React.ReactNode =>
				post.title.raw,
		},
		{
			key: 'donor',
			title: __('Donor', 'kudos-donations'),
			valueCallback: (post: Transaction): React.ReactNode =>
				post.donor?.meta.email ??
				post.donor?.meta?.name ??
				post.donor?.id ??
				'',
		},
		{
			key: 'value',
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
						justify="space-between"
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
			valueCallback: (post: Transaction): string => {
				return post.campaign?.title.raw ?? '';
			},
		},
		{
			key: 'status',
			title: __('Status', 'kudos-donations'),
			valueCallback: (post: Transaction): React.ReactNode => {
				const status = post.meta.status;
				switch (status) {
					case 'paid':
						const url = post.invoice_url;

						return (
							<Button
								variant="secondary"
								href={url}
								target="_blank"
								rel="noreferrer"
								title={__('View invoice', 'kudos-donations')}
							>
								<span
									className="dashicons dashicons-media-document"
									style={{
										marginRight: 4,
										verticalAlign: 'text-top',
									}}
								/>
								{__('Paid', 'kudos-donations')}
							</Button>
						);

					case 'open':
						return __('Open', 'kudos-donations');

					case 'canceled':
						return (
							<>
								{__('Canceled', 'kudos-donations')}{' '}
								<span className="dashicons dashicons-no" />
							</>
						);

					case 'failed':
						return (
							<>
								{__('Failed', 'kudos-donations')}{' '}
								<span className="dashicons dashicons-no" />
							</>
						);

					default:
						return status;
				}
			},
		},
		{
			key: 'method',
			title: __('Method', 'kudos-donations'),
			valueCallback: (post: Transaction): string => post.meta.method,
		},
		{
			key: 'message',
			title: __('Message', 'kudos-donations'),
			valueCallback: (post: Transaction): string => post.meta.message,
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'date',
			valueCallback: (post: Transaction): React.ReactNode => (
				<i>{dateI18n('d-m-Y', post.date, null)}</i>
			),
		},
		{
			key: 'edit',
			title: (
				<VisuallyHidden>{__('Edit', 'kudos-donations')}</VisuallyHidden>
			),
			valueCallback: (post: Campaign): React.ReactNode => (
				<>
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
			),
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
