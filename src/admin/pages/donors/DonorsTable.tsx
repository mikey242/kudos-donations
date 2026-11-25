import { __ } from '@wordpress/i18n';
import React from 'react';
import { createdAtColumn, DetailsModal, Table } from '../../components';
import type { Column, RowActionList } from '../../components';
import { useRowActions } from '../use-row-actions';
import type { Donor } from '../../../types/entity';

const addressLines = (donor: Donor): string[] =>
	[
		donor.business_name,
		donor.street,
		[donor.postcode, donor.city].filter(Boolean).join(' '),
		donor.country,
	].filter(Boolean);

export const DonorsTable = (): React.ReactNode => {
	const rowActions = useRowActions<Donor>();

	const columns: Column<Donor>[] = [
		{
			key: 'name',
			title: __('Name', 'kudos-donations'),
			orderby: 'name',
			render: (donor) => donor.name ?? '',
		},
		{
			key: 'email',
			title: __('Email', 'kudos-donations'),
			orderby: 'email',
			render: (donor) => donor.email ?? '',
		},
		{
			key: 'transaction_count',
			title: __('Total donations', 'kudos-donations'),
			size: 'medium',
			render: (donor) => donor.transaction_count,
		},
		{
			key: 'address',
			title: __('Address', 'kudos-donations'),
			size: 'small',
			render: (donor) => {
				const lines = addressLines(donor);
				return (
					lines.length > 0 && (
						<DetailsModal
							title={__('Address', 'kudos-donations')}
							content={
								<div style={{ fontSize: '16px' }}>
									{lines.map((line) => (
										<div key={line}>{line}</div>
									))}
								</div>
							}
						/>
					)
				);
			},
		},
		createdAtColumn(),
	];

	const actions: RowActionList<Donor> = [
		rowActions.viewTransactions('donor_id'),
		rowActions.inspect(),
		rowActions.remove(
			__('Delete donor', 'kudos-donations'),
			__('Are you sure you wish to delete this donor?', 'kudos-donations')
		),
	];

	return <Table columns={columns} actions={actions} />;
};
