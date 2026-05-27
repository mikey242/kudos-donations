import { SnackbarList } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { NoticesButton } from '../components';
import { useAdminNotices } from '../hooks/use-admin-notices';
import type { ReactNode } from 'react';

export const Notices = (): ReactNode => {
	const { adminNotices, handleRemove } = useAdminNotices();

	const { removeNotice } = useDispatch(noticesStore);
	const snackbarNotices = useSelect(
		(select) =>
			select(noticesStore)
				.getNotices()
				.filter((n) => n.type === 'snackbar'),
		[]
	);

	return (
		<>
			<SnackbarList notices={snackbarNotices} onRemove={removeNotice} />
			<NoticesButton notices={adminNotices} onRemove={handleRemove} />
		</>
	);
};
