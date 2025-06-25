import { Button, Flex, Tooltip, VisuallyHidden } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Table } from '../table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { useEntitiesContext, useSettingsContext } from '../../contexts';
import type { Donor } from '../../../types/entity';
import { useAdminQueryParams } from '../../hooks';
export const DonorsTable = ({ handleEdit }): React.ReactNode => {
	const { settings } = useSettingsContext();
	const { setParams } = useAdminQueryParams();
	const { handleDelete } = useEntitiesContext();

	const changeView = (postId: number) => {
		void setParams({
			page: 'kudos-transactions',
			where: { donor_id: String(postId) },
		});
	};

	const headerItems = [
		{
			key: 'name',
			title: __('Name', 'kudos-donations'),
			valueCallback: (post: Donor): React.ReactNode => post.name ?? '',
		},
		{
			key: 'email',
			title: __('Email', 'kudos-donations'),
			valueCallback: (post: Donor): React.ReactNode => post.email ?? '',
		},
		{
			key: 'value',
			title: __('Total donated', 'kudos-donations'),
			valueCallback: (post: Donor): React.ReactNode => post.total,
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'created_at',
			valueCallback: (post: Donor): React.ReactNode => (
				<Tooltip text={dateI18n('d-m-Y H:i:s', post.created_at, null)}>
					<i>{dateI18n('d-m-Y', post.created_at, null)}</i>
				</Tooltip>
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
			<Table headerItems={headerItems} />
		</>
	);
};
