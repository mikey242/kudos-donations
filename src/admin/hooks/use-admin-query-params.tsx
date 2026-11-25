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

const defaultParams = {
	entity: null,
	tab: null,
	page: '',
	paged: 1,
	panel: null,
	order: 'desc',
	orderby: 'created_at',
	where: {},
};

export const useAdminQueryParams = () => {
	const [params, setQueryParams] = useQueryStates({
		entity: parseAsInteger
			.withDefault(defaultParams.entity)
			.withOptions({ history: 'push' }),

		tab: parseAsString.withDefault(defaultParams.tab),
		page: parseAsString.withDefault(defaultParams.page),
		panel: parseAsString.withDefault(defaultParams.panel),
		paged: parseAsInteger
			.withDefault(defaultParams.paged)
			.withOptions({ history: 'push' }),
		where: parseAsJson(parseWhereSchema)
			.withDefault({})
			.withOptions({ history: 'push' }),
		order: parseAsString.withDefault(defaultParams.order),
		orderby: parseAsString.withDefault(defaultParams.orderby),
	});

	const setParams = useCallback(
		(partial: Partial<typeof defaultParams>) => {
			// Replace all params — reset others to default
			void setQueryParams({ ...defaultParams, ...partial });
		},
		[setQueryParams]
	);

	// Clear the table filters and return to the first page, keeping the sort.
	const resetFilterParams = useCallback(
		() => void setQueryParams({ where: {}, paged: 1 }),
		[setQueryParams]
	);

	return {
		params,
		setParams,
		updateParams: setQueryParams,
		resetFilterParams,
	};
};
