import { Button, Flex, VisuallyHidden } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { DetailsModal, Table } from '../table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { usePostsContext, useSettingsContext } from '../../contexts';
import type { Donor } from '../../../types/posts';
import { useAdminQueryParams } from '../../hooks';
export const DonorsTable = ({ handleEdit }): React.ReactNode => {
	const { settings } = useSettingsContext();
	const { setParams } = useAdminQueryParams();
	const { handleDelete, posts, totalPages, totalItems } = usePostsContext();

	const changeView = (postId: number) => {
		void setParams({
			page: 'kudos-transactions',
			meta_key: 'donor_id',
			meta_value: String(postId),
		});
	};

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
			valueCallback: (post: Donor): React.ReactNode =>
				post.meta.email ?? '',
		},
		{
			key: 'value',
			title: __('Total donated', 'kudos-donations'),
			valueCallback: (post: Donor): React.ReactNode => post.total,
		},
		{
			key: 'address',
			title: __('Address', 'kudos-donations'),
			valueCallback: (post: Donor): React.ReactNode => {
				const hasAddress =
					post.meta.business_name ||
					post.meta.street ||
					post.meta.postcode ||
					post.meta.city ||
					post.meta.country;

				return (
					<>
						{hasAddress && (
							<DetailsModal
								title={__('Address', 'kudos-donations')}
								content={
									<div style={{ fontSize: '16px' }}>
										{post.meta.business_name}
										<br />
										{post.meta.street}
										<br />
										{post.meta.postcode +
											' ' +
											post.meta.city}
										<br />
										{post.meta.country}
									</div>
								}
							/>
						)}
					</>
				);
			},
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'date',
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
						onClick={() => changeView(post.id)}
						// href={`?page=kudos-transactions&meta_key=donor_id&meta_value=${post.id}`}
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
				totalItems={totalItems}
				totalPages={totalPages}
			/>
		</>
	);
};
