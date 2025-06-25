import {
	parseAsInteger,
	parseAsString,
	parseAsJson,
	useQueryStates,
} from 'nuqs';
import { z } from 'zod';
import { useCallback } from '@wordpress/element';

const whereSchema = z.record(z.string(), z.union([z.string(), z.number()]));

const defaultTableFilterParams = {
	order: 'desc',
	orderby: 'created_at',
	search: '',
	where: {},
};

const defaultParams = {
	entity: null,
	tab: null,
	page: '',
	paged: 1,
	...defaultTableFilterParams,
};

export const useAdminQueryParams = () => {
	const [params, setQueryParams] = useQueryStates({
		entity: parseAsInteger
			.withDefault(defaultParams.entity)
			.withOptions({ history: 'push' }),

		tab: parseAsString.withDefault(defaultParams.tab),
		page: parseAsString.withDefault(defaultParams.page),

		paged: parseAsInteger
			.withDefault(defaultParams.paged)
			.withOptions({ history: 'push' }),
		where: parseAsJson(whereSchema.parse)
			.withDefault({})
			.withOptions({ history: 'push' }),
		order: parseAsString.withDefault(defaultParams.order),
		orderby: parseAsString.withDefault(defaultParams.orderby),
		search: parseAsString.withDefault(defaultParams.search),
	});

	const setParams = useCallback(
		(partial: Partial<typeof defaultParams>) => {
			// Replace all params — reset others to default
			void setQueryParams({ ...defaultParams, ...partial });
		},
		[setQueryParams]
	);

	// Detect if any table filters differ from default.
	const hasActiveFilters = Object.entries(defaultTableFilterParams).some(
		([key, defaultValue]) => params[key] !== defaultValue
	);

	// Reset everything to full default (use with caution).
	const resetAllParams = () => void setParams(defaultParams);

	// Reset just the filter params (excluding paged, entity, tab, etc).
	const resetFilterParams = () =>
		void setQueryParams(defaultTableFilterParams);

	return {
		params,
		setParams,
		updateParams: setQueryParams,
		resetAllParams,
		resetFilterParams,
		hasActiveFilters,
	};
};
