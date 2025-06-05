import { parseAsInteger, parseAsString, useQueryStates } from 'nuqs';

const defaultParams = {
	post: null,
	tab: null,
	page: '',
};

export const useAdminQueryParams = () => {
	const [params, setParams] = useQueryStates({
		post: parseAsInteger
			.withDefault(defaultParams.post)
			.withOptions({ history: 'push' }),
		tab: parseAsString.withDefault(defaultParams.tab),
		page: parseAsString.withDefault(defaultParams.page),
	});

	const resetParams = () => void setParams(defaultParams);

	return { params, setParams, resetParams };
};
