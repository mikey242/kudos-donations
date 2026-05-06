import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { NoticeList, SnackbarList } from '@wordpress/components';
import React from 'react';
import { useEffect, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export const Notices = (): React.ReactNode => {
	const { removeNotice, createNotice } = useDispatch(noticesStore);

	const handleRemove = useCallback(
		(id: string) => {
			removeNotice(id);
			apiFetch({
				path: '/kudos/v1/notice/dismiss',
				method: 'POST',
				data: { id },
			}).catch(() => {});
		},
		[removeNotice]
	);

	// Add normal Kudos admin notices found in the window.kudos object.
	useEffect(() => {
		(window.kudos?.admin?.notices ?? []).forEach((notice) => {
			return createNotice(notice.status, notice.content, {
				id: notice.id,
				type: 'default',
				isDismissible: notice.isDismissible,
				onDismiss: () => handleRemove(notice.id),
				__unstableHTML: true,
			});
		});
	}, [createNotice, handleRemove]);

	const notices = useSelect(
		(select) => select(noticesStore).getNotices(),
		[]
	);

	const defaultNotices = notices?.filter((n) => n.type === 'default') ?? [];
	const snackbarNotices = notices?.filter((n) => n.type === 'snackbar') ?? [];

	return (
		<>
			{defaultNotices.length > 0 && (
				<div className="admin-wrap">
					<NoticeList
						notices={defaultNotices}
						onRemove={removeNotice}
					/>
				</div>
			)}
			<SnackbarList notices={snackbarNotices} onRemove={removeNotice} />
		</>
	);
};
