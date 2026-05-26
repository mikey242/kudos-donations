import { useCallback, useEffect } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { store as noticesStore } from '@wordpress/notices';

export const useAdminNotices = () => {
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
			createNotice(notice.status, notice.content, {
				id: notice.id,
				type: 'default',
				isDismissible: notice.isDismissible,
				__unstableHTML: true,
			});
		});
	}, [createNotice]);

	const notices = useSelect(
		(select) => select(noticesStore).getNotices(),
		[]
	);

	return { notices, handleRemove };
};

export type AdminNotice = ReturnType<typeof useAdminNotices>['notices'][number];
