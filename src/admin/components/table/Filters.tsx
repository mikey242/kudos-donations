/* eslint-disable camelcase */
import { Button, Flex, Panel } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { useAdminQueryParams } from '../../hooks';

export interface Filter {
	label: string;
	column: string;
	value: string;
}

interface FiltersProps {
	filters: Filter[];
}

export const Filters = ({ filters }: FiltersProps) => {
	const { updateParams, resetFilterParams, params } = useAdminQueryParams();
	const { column, value } = params;

	if (!filters) {
		return;
	}

	const activeInList = filters.some(
		(filter) => filter.column === column && filter.value === value
	);

	return (
		<Panel>
			<Flex gap={1} align="center" wrap>
				<Button
					size="compact"
					isPressed={!column && !value}
					onClick={resetFilterParams}
				>
					{__('All', 'kudos-donations')}
				</Button>
				{filters?.map((filter: Filter) => (
					<Button
						size="compact"
						key={`${filter.column}:${filter.value}`}
						isPressed={
							column === filter.column && value === filter.value
						}
						onClick={() =>
							updateParams({
								column: filter.column,
								value: filter.value,
								paged: 1,
							})
						}
					>
						{filter.label}
					</Button>
				))}
				{column && value && !activeInList && (
					<Button
						size="compact"
						isPressed
						onClick={() =>
							updateParams({
								column,
								value,
							})
						}
					>
						{`${column}: ${value}`}
					</Button>
				)}
			</Flex>
		</Panel>
	);
};
