/* eslint-disable camelcase */
import { Button, Flex, Panel } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { useAdminQueryParams } from '../../hooks';

export interface Filter {
	label: string;
	where: Record<string, string>;
}

interface FiltersProps {
	filters: Filter[];
}

export const Filters = ({ filters }: FiltersProps) => {
	const { updateParams, resetFilterParams, params } = useAdminQueryParams();
	const currentWhere = params.where || {};

	if (!filters) {
		return;
	}

	const activeFilter = filters.find((filter) =>
		Object.entries(filter.where).every(
			([key, value]) => currentWhere[key] === value
		)
	);

	const handleClick = (filter?: Filter) => {
		if (!filter) {
			resetFilterParams();
			return;
		}

		void updateParams({
			where: { ...params.where, ...filter.where },
			paged: 1,
		});
	};

	// For displaying unknown filters
	const knownKeys = filters.flatMap((f) => Object.keys(f.where));
	const otherFilters = Object.entries(currentWhere).filter(
		([key]) => !knownKeys.includes(key)
	);

	return (
		<Panel>
			<Flex gap={1} align="center" wrap>
				<Button
					size="compact"
					isPressed={Object.keys(currentWhere).length === 0}
					onClick={resetFilterParams}
				>
					{__('All', 'kudos-donations')}
				</Button>
				{filters?.map((filter: Filter) => (
					<Button
						size="compact"
						key={JSON.stringify(filter.where)}
						isPressed={activeFilter === filter}
						onClick={() => handleClick(filter)}
					>
						{filter.label}
					</Button>
				))}
				{otherFilters.map(([key, value]) => (
					<Button
						size="compact"
						variant="primary"
						key={`${key}:${value}`}
						icon="dismiss"
						iconSize={15}
						onClick={() =>
							updateParams({
								where: Object.fromEntries(
									Object.entries(currentWhere).filter(
										([k]) => k !== key
									)
								),
								paged: 1,
							})
						}
					>
						{`${key}: ${value}`}
					</Button>
				))}
			</Flex>
		</Panel>
	);
};
