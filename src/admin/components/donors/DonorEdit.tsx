import type { BaseEntity } from '../../../types/entity';
import React from 'react';
import { SingleEntityEdit } from '../SingleEntityEdit';

interface PostEditProps {
	entity: BaseEntity;
}
export const DonorEdit = ({ entity }: PostEditProps): React.ReactNode => {
	if (!entity) {
		return null;
	}

	return (
		<SingleEntityEdit
			data={entity}
			fields={[
				{
					id: 'title',
					label: 'Title',
					type: 'text',
				},
				{
					id: 'name',
					label: 'Name',
					type: 'text',
				},
				{
					id: 'business_name',
					label: 'Business name',
					type: 'text',
				},
				{
					id: 'street',
					label: 'Street',
					type: 'text',
				},
				{
					id: 'city',
					label: 'City',
					type: 'text',
				},
				{
					id: 'postcode',
					label: 'Transaction id',
					type: 'text',
				},
				{
					id: 'country',
					label: 'Country',
					type: 'text',
				},
				{
					id: 'vendor_customer_id',
					label: 'Vendor customer id',
					type: 'text',
				},
				{
					id: 'locale',
					label: 'Locale',
					type: 'text',
				},
				{
					id: 'created_at',
					label: 'Created at',
					type: 'datetime',
				},
			]}
			form={{
				fields: [
					'title',
					'name',
					'business_name',
					'street',
					'city',
					'postcode',
					'country',
					'vendor_customer_id',
					'locale',
					'created_at',
				],
			}}
		/>
	);
};
