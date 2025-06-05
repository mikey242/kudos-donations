import { parseAsInteger, parseAsString, useQueryStates } from 'nuqs';

const defaultFilterParams = {
	order: 'desc',
	orderby: 'date',
	meta_key: '',
	meta_value: '',
	meta_query: '',
	metaType: '',
	search: '',
};

const defaultParams = {
	...defaultFilterParams,
	paged: 1,
};

export const useAdminTableParams = () => {
	const [params, setParams] = useQueryStates({
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

	// Check to see if any values differ from default.
	const hasActiveFilters = Object.entries(defaultFilterParams).some(
		([key, defaultValue]) => params[key] !== defaultValue
	);

	// Reset to default values.
	const resetParams = () => setParams(defaultFilterParams);

	return { params, setParams, resetParams, hasActiveFilters };
};
