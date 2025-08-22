import type { BaseEntity } from '../../../types/entity';
import React from 'react';
import { SingleEntityEdit } from '../SingleEntityEdit';

interface PostEditProps {
	entity: BaseEntity;
}
export const SubscriptionEdit = ({
	entity,
}: PostEditProps): React.ReactNode => {
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
					id: 'value',
					label: 'Value',
					type: 'text',
				},
				{
					id: 'currency',
					label: 'Currency',
					type: 'text',
				},
				{
					id: 'status',
					label: 'Status',
					type: 'text',
				},
				{
					id: 'frequency',
					label: 'Frequency',
					type: 'text',
				},
				{
					id: 'years',
					label: 'Years',
					type: 'integer',
				},
				{
					id: 'transaction_id',
					label: 'Transaction id',
					type: 'text',
				},
				{
					id: 'vendor_customer_id',
					label: 'Vendor customer id',
					type: 'text',
				},
				{
					id: 'vendor_subscription_id',
					label: 'Vendor subscription id',
					type: 'text',
				},
				{
					id: 'campaign_id',
					label: 'Campaign id',
					type: 'integer',
				},
				{
					id: 'donor_id',
					label: 'Donor id',
					type: 'integer',
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
					'value',
					'currency',
					'status',
					'frequency',
					'years',
					'transaction_id',
					'vendor_customer_id',
					'vendor_subscription_id',
					'campaign_id',
					'donor_id',
					'created_at',
				],
			}}
		/>
	);
};
