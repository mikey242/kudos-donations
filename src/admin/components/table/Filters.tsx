/* eslint-disable camelcase */
import { Button, Flex, Panel } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { useAdminQueryParams } from '../../hooks';

export interface Filter {
	label: string;
	meta_key: string;
	meta_value: string;
}

interface FiltersProps {
	filters: Filter[];
}

export const Filters = ({ filters }: FiltersProps) => {
	const { updateParams, resetFilterParams, params } = useAdminQueryParams();
	const { meta_key, meta_value } = params;

	if (!filters) {
		return;
	}

	const activeInList = filters.some(
		(filter) =>
			filter.meta_key === meta_key && filter.meta_value === meta_value
	);

	return (
		<Panel>
			<Flex gap={1} align="center" wrap>
				<Button
					size="compact"
					isPressed={!meta_key && !meta_value}
					onClick={resetFilterParams}
				>
					{__('All', 'kudos-donations')}
				</Button>
				{filters?.map((filter: Filter) => (
					<Button
						size="compact"
						key={`${filter.meta_key}:${filter.meta_value}`}
						isPressed={
							meta_key === filter.meta_key &&
							meta_value === filter.meta_value
						}
						onClick={() =>
							updateParams({
								meta_key: filter.meta_key,
								meta_value: filter.meta_value,
								paged: 1,
							})
						}
					>
						{filter.label}
					</Button>
				))}
				{meta_key && meta_value && !activeInList && (
					<Button
						size="compact"
						isPressed
						onClick={() =>
							updateParams({
								meta_key,
								meta_value,
							})
						}
					>
						{`${meta_key}: ${meta_value}`}
					</Button>
				)}
			</Flex>
		</Panel>
	);
};
