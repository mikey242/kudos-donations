import { parseAsInteger, parseAsString, useQueryStates } from 'nuqs';

const defaultParams = {
	post: null,
	tab: null,
	paged: 1,
	page: '',
};

export const useAdminQueryParams = () => {
	const [params, setParams] = useQueryStates({
		post: parseAsInteger
			.withDefault(defaultParams.post)
			.withOptions({ history: 'push' }),
		tab: parseAsString.withDefault(defaultParams.tab),
		paged: parseAsInteger
			.withDefault(defaultParams.paged)
			.withOptions({ history: 'push' }),
		page: parseAsString.withDefault(defaultParams.page),
	});

	const resetParams = () => void setParams(defaultParams);

	return { params, setParams, resetParams };
};
