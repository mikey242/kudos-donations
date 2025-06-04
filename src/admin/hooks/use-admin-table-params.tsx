import { parseAsInteger, parseAsString, useQueryStates } from 'nuqs';

const defaultParams = {
	paged: 1,
	order: 'desc',
	orderby: 'date',
	metaKey: '',
	metaValue: '',
	metaCompare: '=',
	metaType: 'string',
	search: '',
};

export const useAdminTableParams = () => {
	const [params, setParams] = useQueryStates({
		paged: parseAsInteger.withDefault(defaultParams.paged),
		order: parseAsString.withDefault(defaultParams.order),
		orderby: parseAsString.withDefault(defaultParams.orderby),
		metaKey: parseAsString.withDefault(defaultParams.metaKey),
		metaValue: parseAsString.withDefault(defaultParams.metaValue),
		metaCompare: parseAsString.withDefault(defaultParams.metaCompare),
		metaType: parseAsString.withDefault(defaultParams.metaType),
		search: parseAsString.withDefault(defaultParams.search),
	});

	// Don't include page number in reset logic.
	delete defaultParams.paged;

	// Check to see if any values differ from default.
	const hasActiveFilters = Object.entries(defaultParams).some(
		([key, defaultValue]) => params[key] !== defaultValue
	);

	// Reset to default values.
	const resetParams = () => void setParams(defaultParams);

	return { params, setParams, resetParams, hasActiveFilters };
};
