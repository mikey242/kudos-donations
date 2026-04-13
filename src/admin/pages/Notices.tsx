import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { NoticeList, SnackbarList } from '@wordpress/components';
import React from 'react';
import { useEffect } from '@wordpress/element';

export const Notices = (): React.ReactNode => {
	const { removeNotice, createNotice } = useDispatch(noticesStore);

	// Add normal Kudos admin notices found in the window.kudos object.
	useEffect(() => {
		(window.kudos?.notices ?? []).forEach((notice) => {
			return createNotice(notice.status, notice.content, {
				id: notice.id,
				type: 'default',
				isDismissible: notice.isDismissible,
			});
		});
	}, [createNotice]);

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
			</div>
			{/* @ts-ignore */}
			<SnackbarList notices={snackbarNotices} onRemove={removeNotice} />
		</>
	);
};
