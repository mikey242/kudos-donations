import { Button, Flex, VisuallyHidden } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Table } from '../table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { usePostsContext, useSettingsContext } from '../../contexts';
import type { Donor } from '../../../types/posts';
export const DonorsTable = ({ handleEdit }): React.ReactNode => {
	const { settings } = useSettingsContext();
	const {
		handleDelete,
		isLoading,
		hasLoadedOnce,
		posts,
		totalPages,
		totalItems,
	} = usePostsContext();

	const headerItems = [
		{
			key: 'name',
			title: __('Name', 'kudos-donations'),
			valueCallback: (post: Donor): React.ReactNode =>
				post.meta.name ?? '',
		},
		{
			key: 'email',
			title: __('Email', 'kudos-donations'),
			orderby: 'meta_value',
			valueCallback: (post: Donor): React.ReactNode =>
				post.meta.email ?? '',
		},
		{
			key: 'value',
			title: __('Total donated', 'kudos-donations'),
			valueCallback: (post: Donor): React.ReactNode => post.total,
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'date',
			width: '10%',
			valueCallback: (post: Donor): React.ReactNode => (
				<i>{dateI18n('d-m-Y', post.date, null)}</i>
			),
		},
		{
			key: 'edit',
			title: (
				<VisuallyHidden>{__('Edit', 'kudos-donations')}</VisuallyHidden>
			),
			valueCallback: (post: Donor): React.ReactNode => (
				<Flex justify="flex-end">
					<Button
						size="compact"
						icon="money-alt"
						href={`?page=kudos-transactions&metaKey=donor_id&metaValue=${post.id}&metaCompare=%3D`}
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
