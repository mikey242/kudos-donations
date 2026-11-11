import { useEffect } from '@wordpress/element';

/**
 * Keeps the highlighted item in WordPress's admin menu in step with the Kudos page shown.
 *
 * Kudos switches pages without reloading, but WordPress marks the current submenu item
 * only when the page loads, so the highlight would stay on the page first opened.
 *
 * @param page The `page` query parameter of the page shown, e.g. "kudos-campaigns".
 */
export const useWpMenuSync = (page: string): void => {
	useEffect(() => {
		const submenu = document.querySelector(
			'#adminmenu li.wp-has-current-submenu > ul.wp-submenu'
		);

		// The links' hrefs are the source: Plus can add or reorder pages.
		const links = submenu?.querySelectorAll<HTMLAnchorElement>('li > a');
		if (
			!links ||
			!Array.from(links).some((link) => linkPage(link) === page)
		) {
			return;
		}

		links.forEach((link) => {
			const isCurrent = linkPage(link) === page;
			link.classList.toggle('current', isCurrent);
			link.parentElement?.classList.toggle('current', isCurrent);
			if (isCurrent) {
				link.setAttribute('aria-current', 'page');
			} else {
				link.removeAttribute('aria-current');
			}
		});
	}, [page]);
};

const linkPage = (link: HTMLAnchorElement): string | null =>
	new URL(link.href, window.location.href).searchParams.get('page');
