import { Button, Dashicon, Flex, VisuallyHidden } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Table } from '../table/Table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { useEffect } from '@wordpress/element';
import type { Subscription } from '../../../types/posts';
import { IconKey } from '@wordpress/components/build-types/dashicon/types';
import { useAdminContext, usePostsContext } from '../../contexts';
export const SubscriptionsTable = ({ handleEdit }): React.ReactNode => {
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
		setPageTitle(__('Your subscriptions', 'kudos-donations'));
	}, [setPageTitle]);

	const headerItems = [
		{
			key: 'donor',
			title: __('Donor', 'kudos-donations'),
			valueCallback: (post: Subscription): React.ReactNode =>
				post.donor?.meta?.name ?? post.donor?.meta.email ?? '',
		},
		{
			key: 'status',
			title: __('Status', 'kudos-donations'),
			width: '7%',
			valueCallback: (post: Subscription): React.ReactNode => {
				const status = post.meta?.status;

				const statusConfig: Record<
					string,
					{ title: string; icon: string }
				> = {
					active: {
						title: __('Active', 'kudos-donations'),
						icon: 'yes-alt',
					},
					canceled: {
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
			key: 'amount',
			orderby: 'meta_value_num',
			title: __('Amount', 'kudos-donations'),
			valueCallback: (post: Subscription): React.ReactNode => {
				const value = post.meta?.value;
				const currency = post.meta?.currency;

				if (!value || !currency) {
					return null;
				}

				const currencySymbol =
					currencies[post.meta?.currency] ?? currency;
				return (
					<span>
						{currencySymbol}
						{value}
					</span>
				);
			},
		},
		{
			key: 'frequency',
			title: __('Frequency', 'kudos-donations'),
			valueCallback: (post: Subscription): React.ReactNode =>
				post.meta.frequency,
		},
		{
			key: 'length',
			title: __('Length', 'kudos-donations'),
			valueCallback: (post: Subscription): React.ReactNode =>
				post.meta.years,
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'date',
			width: '10%',
			valueCallback: (post: Subscription): React.ReactNode => (
				<i>{dateI18n('d-m-Y', post.date, null)}</i>
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
						href={`?page=kudos-transactions&metaKey=donor_id&metaValue=${post.donor?.id}&metaCompare=%3D`}
						title={__('View donations', 'kudos-donations')}
					/>
					<Button
						size="compact"
						icon="media-document"
						onClick={() => handleEdit(post.id)}
						title={__('View more', 'kudos-donations')}
					/>
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
