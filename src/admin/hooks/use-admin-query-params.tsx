import {
	parseAsInteger,
	parseAsString,
	parseAsJson,
	useQueryStates,
} from 'nuqs';
import { useCallback } from '@wordpress/element';

type WhereRecord = Record<string, string | number>;

const parseWhereSchema = (value: unknown): WhereRecord => {
	if (typeof value !== 'object' || value === null) {
		return {};
	}
	const result: WhereRecord = {};
	for (const [key, val] of Object.entries(value)) {
		if (typeof val === 'string' || typeof val === 'number') {
			result[key] = val;
		}
	}
	return result;
};

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
		where: parseAsJson(parseWhereSchema)
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
