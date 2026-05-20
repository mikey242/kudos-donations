import { useLayoutEffect } from '@wordpress/element';
import { useAdminContext } from '../contexts';

export function usePageTitle(title: string | null) {
	const { setPageTitle } = useAdminContext();
	useLayoutEffect(() => {
		setPageTitle(title);
		return () => setPageTitle(null);
	}, [title, setPageTitle]);
}
