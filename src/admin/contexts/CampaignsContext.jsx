import React from 'react';
import {
	createContext,
	useCallback,
	useContext,
	useEffect,
	useState,
} from '@wordpress/element';
// eslint-disable-next-line import/default
import { useEntityRecords } from '@wordpress/core-data';
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Icon } from '@wordpress/components';

export const CampaignsContext = createContext(null);

export default function CampaignsProvider({
	postType = 'kudos_campaign',
	children,
}) {
	const [sortQuery, setSortQuery] = useState(getSortParams());
	const { saveEntityRecord, deleteEntityRecord } = useDispatch('core');
	const { createSuccessNotice, createErrorNotice, removeAllNotices } =
		useDispatch(noticesStore);
	const [cachedPosts, setCachedPosts] = useState([]);
	const { records: posts, hasResolved } = useEntityRecords(
		'postType',
		postType,
		{
			per_page: 10,
			order: sortQuery.order,
			orderby: sortQuery.orderby,
		}
	);

	useEffect(() => {
		if (hasResolved) {
			setCachedPosts(posts);
		}
	}, [posts, hasResolved]);

	const handleSave = useCallback(
		(args = {}) => {
			return removeAllNotices().then(() => {
				return saveEntityRecord(
					'postType',
					'kudos_campaign',
					args
				).catch((error) => {
					void createErrorNotice(
						sprintf(
							/* translators: %s is the error message. */
							__(
								'Error creating campaign: %s',
								'kudos-donations'
							),
							error.message
						)
					);
				});
			});
		},
		[createErrorNotice, removeAllNotices, saveEntityRecord]
	);

	const handleUpdate = (data) => {
		return handleSave(data).then((response) => {
			void createSuccessNotice(
				__('Campaign updated', 'kudos-donations'),
				{ type: 'snackbar', icon: <Icon icon="saved" /> }
			);
			return response;
		});
	};

	// Handles creating a campaign.
	const handleNew = (args) => {
		// If args is a SyntheticEvent, ignore it and create an empty args object
		if (args.preventDefault) {
			args = {};
		}

		// Set default arguments.
		const {
			title = __('New Campaign', 'kudos-donations'),
			status = 'publish',
			...other
		} = args;

		return handleSave({
			title,
			status,
			...other,
		}).then(() => {
			void createSuccessNotice(
				__('Campaign created', 'kudos-donations'),
				{ type: 'snackbar', icon: <Icon icon="plus" /> }
			);
		});
	};

	const handleDelete = useCallback(
		(postId) => {
			deleteEntityRecord('postType', 'kudos_campaign', postId, {
				force: true,
			})
				.then(() => {
					void createSuccessNotice(
						__('Campaign deleted', 'kudos-donations'),
						{ type: 'snackbar', icon: <Icon icon="trash" /> }
					);
				})
				.catch((error) => {
					void createErrorNotice(
						sprintf(
							/* translators: %s is the error message. */
							__(
								'Error deleting campaign: %s',
								'kudos-donations'
							),
							error
						)
					);
				});
		},
		[createErrorNotice, createSuccessNotice, deleteEntityRecord]
	);

	// Prepares data for duplicating current post.
	const handleDuplicate = (post) => {
		delete post.id;

		const data = {
			...post,
			title: post.title.rendered,
			date: new Date(),
		};
		handleSave(data).then(() =>
			createSuccessNotice(__('Campaign duplicated', 'kudos-donations'), {
				type: 'snackbar',
				icon: <Icon icon="media-document" />,
			})
		);
	};

	const sort = (orderby) => {
		setSortQuery((prev) => {
			return {
				orderby,
				order:
					prev.orderby !== orderby || prev.order === 'desc'
						? 'asc'
						: 'desc',
			};
		});
	};

	useEffect(() => {
		updateQueryVars(sortQuery);
	}, [sortQuery]);

	const data = {
		posts: cachedPosts,
		sort,
		sortQuery,
		hasResolved,
		handleNew,
		handleDuplicate,
		handleDelete,
		handleUpdate,
	};

	return (
		<>
			<CampaignsContext.Provider value={data}>
				{children}
			</CampaignsContext.Provider>
		</>
	);
}

const getSortParams = () => {
	const params = new URLSearchParams(window.location.search);
	return {
		order: params.get('order') ?? 'desc',
		orderby: params.get('orderby') ?? 'date',
	};
};

const updateQueryVars = (sortQuery) => {
	const params = new URLSearchParams(window.location.search);
	params.set('order', sortQuery.order);
	params.set('orderby', sortQuery.orderby);
	const newUrl = `${window.location.pathname}?${params.toString()}`;
	window.history.replaceState(null, '', newUrl);
};

export const useCampaignsContext = () => {
	return useContext(CampaignsContext);
};
