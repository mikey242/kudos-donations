import { useCallback, useEffect } from '@wordpress/element';
import { useDispatch, useSelect, select as storeSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { store as noticesStore } from '@wordpress/notices';
import type { KudosNotice } from '../../types/window-kudos';
import { useOnSettingsSaved } from './use-on-settings-saved';

export const useAdminNotices = () => {
	const { createNotice, removeNotice } = useDispatch(noticesStore);

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

	// Fetches the current server notices, clears any existing default notices,
	const fetchAndSyncNotices = useCallback(() => {
		apiFetch<KudosNotice[]>({ path: '/kudos/v1/notice' })
			.then((fresh) => {
				storeSelect(noticesStore)
					.getNotices()
					.filter((n) => n.type === 'default')
					.forEach((n) => removeNotice(n.id));
				fresh.forEach((notice) =>
					createNotice(notice.status, notice.content, {
						id: notice.id,
						type: 'default',
						isDismissible: notice.isDismissible,
						__unstableHTML: true,
					})
				);
			})
			.catch(() => {});
	}, [createNotice, removeNotice]);

	useEffect(() => {
		fetchAndSyncNotices();
	}, [fetchAndSyncNotices]);

	useOnSettingsSaved(fetchAndSyncNotices);

	const adminNotices = useSelect(
		(select) =>
			select(noticesStore)
				.getNotices()
				.filter((n) => n.type === 'default'),
		[]
	);

	return { adminNotices, handleRemove };
};

export type AdminNotice = ReturnType<
	typeof useAdminNotices
>['adminNotices'][number];
