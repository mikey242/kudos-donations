import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import type { BaseEntity } from '../../types/entity';

export interface QueryArgs {
	page?: number;
	per_page?: number;
	columns?: string[];
	orderby?: string;
	order?: string;
	where?: Record<string, string | number>;
	[key: string]: unknown;
}

export interface EntityRestResponse<T extends BaseEntity> {
	items: T[];
	total: number;
	total_pages: number;
}

export async function queryEntities<T extends BaseEntity>(
	entityType: string,
	args: QueryArgs
): Promise<EntityRestResponse<T>> {
	let response: Response;
	try {
		response = await apiFetch({
			path: addQueryArgs(`/kudos/v1/${entityType}`, args),
			parse: false,
		});
	} catch (e: any) {
		// apiFetch rejects with the raw Response on non-ok status
		if (e instanceof Response) {
			throw await e.json();
		}
		throw e;
	}
	const items: T[] = await response.json();
	return {
		items,
		total: parseInt(response.headers.get('X-WP-Total') ?? '0', 10),
		total_pages: parseInt(
			response.headers.get('X-WP-TotalPages') ?? '1',
			10
		),
	};
}
