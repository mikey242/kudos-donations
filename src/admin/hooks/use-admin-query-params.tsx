import { parseAsInteger, parseAsString, useQueryStates } from 'nuqs';
import { useCallback } from '@wordpress/element';

const defaultTableFilterParams = {
	order: 'desc',
	orderby: 'date',
	meta_key: '',
	meta_value: '',
	meta_query: '',
	metaType: '',
	search: '',
};

const defaultParams = {
	post: null,
	tab: null,
	page: '',
	paged: 1,
	...defaultTableFilterParams,
};

export const useAdminQueryParams = () => {
	const [params, setQueryParams] = useQueryStates({
		post: parseAsInteger
			.withDefault(defaultParams.post)
			.withOptions({ history: 'push' }),

		tab: parseAsString.withDefault(defaultParams.tab),
		page: parseAsString.withDefault(defaultParams.page),

		paged: parseAsInteger
			.withDefault(defaultParams.paged)
			.withOptions({ history: 'push' }),

		order: parseAsString.withDefault(defaultParams.order),
		orderby: parseAsString.withDefault(defaultParams.orderby),
		meta_key: parseAsString.withDefault(defaultParams.meta_key),
		meta_value: parseAsString.withDefault(defaultParams.meta_value),
		meta_query: parseAsString.withDefault(defaultParams.meta_query),
		metaType: parseAsString.withDefault(defaultParams.metaType),
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

	// Reset just the filter params (excluding paged, post, tab, etc).
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
