import { SnackbarList } from '@wordpress/components';
import type { ReactNode } from 'react';
import { useAdminNotices } from '../hooks/use-admin-notices';
import { NoticesButton } from '../components';

export const Notices = (): ReactNode => {
	const { notices, handleRemove } = useAdminNotices();
	const snackbarNotices = notices?.filter((n) => n.type === 'snackbar') ?? [];
	const defaultNotices = notices.filter((n) => n.type === 'default');

	return (
		<>
			<SnackbarList notices={snackbarNotices} onRemove={handleRemove} />
			<NoticesButton notices={defaultNotices} onRemove={handleRemove} />
		</>
	);
};
