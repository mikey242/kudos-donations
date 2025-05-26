import { Button, Flex, VisuallyHidden } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Table } from '../table/Table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { useAdminContext, usePostsContext } from '../contexts';
import { useEffect } from '@wordpress/element';
import type { Subscription } from '../../../types/posts';
export const SubscriptionsTable = (): React.ReactNode => {
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
			key: 'description',
			title: __('Description', 'kudos-donations'),
			orderby: 'title',
			width: '25%',
			valueCallback: (post: Subscription): React.ReactNode =>
				post.title.raw ?? '',
		},
		{
			key: 'donor',
			title: __('Donor', 'kudos-donations'),
			valueCallback: (post: Subscription): React.ReactNode =>
				post.donor?.meta.name ?? post.donor?.meta.email ?? '',
		},
		{
			key: 'amount',
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
			key: 'status',
			title: __('Status', 'kudos-donations'),
			valueCallback: (post: Subscription): React.ReactNode =>
				post.meta.status,
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'date',
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
