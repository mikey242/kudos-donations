import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { NoticeList, SnackbarList } from '@wordpress/components';
import React from 'react';

export const Notices = (): React.ReactNode => {
	const { removeNotice } = useDispatch(noticesStore);

	const notices = useSelect(
		(select) => select(noticesStore).getNotices(),
		[]
	);

	const defaultNotices = notices?.filter((n) => n.type === 'default') ?? [];
	const snackbarNotices = notices?.filter((n) => n.type === 'snackbar') ?? [];

	return (
		<>
			<div className="admin-wrap">
				{/* @ts-ignore */}
				<NoticeList notices={defaultNotices} onRemove={removeNotice} />
				<div className="wp-header-end" />
			</div>
			{/* @ts-ignore */}
			<SnackbarList notices={snackbarNotices} onRemove={removeNotice} />
		</>
	);
};
