/**
 * Builds the URL of a Kudos REST route, e.g. `kudosRestUrl('campaign/12')`.
 *
 * Uses the REST root WordPress reports for this site (`rest_url()`, passed in as
 * `window.kudos.front.restRoot`). With plain permalinks that is
 * `…/index.php?rest_route=/`: the `/wp-json/` path does not exist there, and WordPress
 * redirects it to an HTML page. For query arguments, wrap the result in `addQueryArgs()`
 * from `@wordpress/url`, which handles the `?` already in such a root.
 *
 * @param route Route below `kudos/v1/`, without a leading slash.
 */
export const kudosRestUrl = (route: string): string => {
	const root = window.kudos?.front?.restRoot ?? '/wp-json/';

	return `${root.replace(/\/$/, '')}/kudos/v1/${route.replace(/^\//, '')}`;
};
