import type { BaseEntity, Campaign } from '../../../types/entity';
import React from 'react';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { EntityRestResponse } from '../../contexts';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { SingleEntityEdit } from '../SingleEntityEdit';

interface PostEditProps {
	entity: BaseEntity;
}

interface Element {
	value: any;
	label: string;
}

export const TransactionEdit = ({ entity }: PostEditProps): React.ReactNode => {
	const [campaigns, setCampaigns] = useState<Element[]>();

	const fetchPosts = useCallback(async () => {
		const response: EntityRestResponse<Campaign> = await apiFetch({
			path: addQueryArgs('/kudos/v1/campaign', {
				columns: ['id', 'title'],
			}),
		});
		const campaignsResponse = response.items.map((item: Campaign) => {
			return {
				value: item.id,
				label: `${item.title} (id: ${item.id})`,
			};
		});
		setCampaigns(campaignsResponse);
	}, []);

	useEffect(() => {
		void fetchPosts();
	}, [fetchPosts]);

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
					id: 'sequence_type',
					label: 'Sequence type',
					type: 'text',
				},
				{
					id: 'invoice_number',
					label: 'Invoice number',
					type: 'integer',
				},
				{
					id: 'vendor',
					label: 'Vendor',
					type: 'text',
				},
				{
					id: 'vendor_customer_id',
					label: 'Vendor customer id',
					type: 'text',
				},
				{
					id: 'vendor_payment_id',
					label: 'Vendor payment id',
					type: 'text',
				},
				{
					elements: campaigns,
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
					id: 'subscription_id',
					label: 'Subscription id',
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
					'sequence_type',
					'invoice_number',
					'vendor',
					'vendor_customer_id',
					'vendor_payment_id',
					'campaign_id',
					'donor_id',
					'subscription_id',
					'created_at',
				],
			}}
		/>
	);
};
