import { parseAsInteger, parseAsString, useQueryStates } from 'nuqs';

const defaultFilterParams = {
	order: 'desc',
	orderby: 'date',
	metaKey: '',
	metaValue: '',
	metaCompare: '=',
	metaType: 'string',
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
		metaKey: parseAsString.withDefault(defaultParams.metaKey),
		metaValue: parseAsString.withDefault(defaultParams.metaValue),
		metaCompare: parseAsString.withDefault(defaultParams.metaCompare),
		metaType: parseAsString.withDefault(defaultParams.metaType),
		search: parseAsString.withDefault(defaultParams.search),
	});

	// Check to see if any values differ from default.
	const hasActiveFilters = Object.entries(defaultFilterParams).some(
		([key, defaultValue]) => params[key] !== defaultValue
	);

	// Reset to default values.
	const resetParams = () => void setParams(defaultFilterParams);

	return { params, setParams, resetParams, hasActiveFilters };
};
