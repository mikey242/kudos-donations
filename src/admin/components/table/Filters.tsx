import { Button, Flex, Panel } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useAdminQueryParams } from '../../hooks';
import type { WhereRecord } from '../../hooks/use-admin-query-params';

export interface Filter {
	label: string;
	where: Record<string, string>;
}

// Keys the filter buttons control; anything else in `where` came from elsewhere.
const filterKeys = (filters: Filter[] = []): string[] =>
	filters.flatMap((filter) => Object.keys(filter.where));

const withoutKeys = (where: WhereRecord, keys: string[]): WhereRecord =>
	Object.fromEntries(
		Object.entries(where).filter(([key]) => !keys.includes(key))
	);

interface FiltersProps {
	filters?: Filter[];
}

export const Filters = ({ filters }: FiltersProps) => {
	const { updateParams, params } = useAdminQueryParams();
	const currentWhere = params.where || {};

	if (!filters) {
		return null;
	}

	const keys = filterKeys(filters);
	const activeFilter = filters.find((filter) =>
		Object.entries(filter.where).every(
			([key, value]) => currentWhere[key] === value
		)
	);

	// Both keep any context filter (e.g. donor_id) shown by FilterContext.
	const applyFilter = (filter?: Filter) =>
		void updateParams({
			where: { ...withoutKeys(currentWhere, keys), ...filter?.where },
			paged: 1,
		});

	return (
		<Panel>
			<Flex gap={1} align="center" wrap>
				<Button
					size="compact"
					isPressed={!activeFilter}
					onClick={() => applyFilter()}
				>
					{__('All', 'kudos-donations')}
				</Button>
				{filters.map((filter) => (
					<Button
						size="compact"
						key={JSON.stringify(filter.where)}
						isPressed={activeFilter === filter}
						onClick={() => applyFilter(filter)}
					>
						{filter.label}
					</Button>
				))}
			</Flex>
		</Panel>
	);
};

const describeContext = (key: string, rawValue: string | number): string => {
	const value = String(rawValue);
	switch (key) {
		case 'donor_id':
			return sprintf(
				/* translators: %s is the donor ID */
				__('Showing donations from donor #%s', 'kudos-donations'),
				value
			);
		case 'campaign_id':
			return sprintf(
				/* translators: %s is the campaign ID */
				__('Showing donations to campaign #%s', 'kudos-donations'),
				value
			);
		case 'subscription_id':
			return sprintf(
				/* translators: %s is the subscription ID */
				__('Showing donations for subscription #%s', 'kudos-donations'),
				value
			);
		default:
			return `${key}: ${value}`;
	}
};

export const FilterContext = ({ filters }: FiltersProps) => {
	const { updateParams, params } = useAdminQueryParams();
	const currentWhere = params.where || {};
	const keys = filterKeys(filters);
	const contextEntries = Object.entries(currentWhere).filter(
		([key]) => !keys.includes(key)
	);

	if (contextEntries.length === 0) {
		return null;
	}

	const clearContext = () =>
		void updateParams({
			where: withoutKeys(
				currentWhere,
				contextEntries.map(([key]) => key)
			),
			paged: 1,
		});

	return (
		<Flex className="kudos-filter-context" justify="flex-start" gap={2}>
			<span>
				{contextEntries
					.map(([key, value]) => describeContext(key, value))
					.join(', ')}
			</span>
			<span aria-hidden="true">·</span>
			<Button variant="link" onClick={clearContext}>
				{__('Show all', 'kudos-donations')}
			</Button>
		</Flex>
	);
};
