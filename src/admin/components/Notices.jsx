import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { useMemo } from '@wordpress/element';
import { NoticeList, SnackbarList } from '@wordpress/components';

export const Notices = () => {
	const { removeNotice } = useDispatch(noticesStore);
	const notices = useSelect(
		(select) => select(noticesStore).getNotices(),
		[]
	);
	const snackbarNotices = useMemo(
		() => notices?.filter((el) => el.type === 'snackbar'),
		[notices]
	);
	const defaultNotices = useMemo(
		() => notices?.filter((el) => el.type === 'default'),
		[notices]
	);

	return (
		<>
			<div className="admin-wrap">
				<NoticeList notices={defaultNotices} onRemove={removeNotice} />
				<div className="wp-header-end"></div>
			</div>
			<SnackbarList notices={snackbarNotices} onRemove={removeNotice} />
		</>
	);
};
