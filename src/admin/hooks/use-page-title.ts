import { useLayoutEffect } from '@wordpress/element';
import type { ReactNode } from 'react';
import { useAdminContext } from '../contexts';

export function usePageTitle(title: ReactNode) {
	const { setPageTitle } = useAdminContext();
	useLayoutEffect(() => {
		setPageTitle(title);
		return () => setPageTitle(null);
	}, [title, setPageTitle]);
}
