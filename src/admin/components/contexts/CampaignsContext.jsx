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
import { useAdminContext } from './AdminContext';

const CampaignsContext = createContext(null);

export const CampaignsProvider = ({
	postType = 'kudos_campaign',
	children,
}) => {
	const { searchParams } = useAdminContext();
	const { saveEntityRecord, deleteEntityRecord } = useDispatch('core');
	const { createSuccessNotice, createErrorNotice, removeAllNotices } =
		useDispatch(noticesStore);
	const [cachedPosts, setCachedPosts] = useState([]);
	const { records: posts, hasResolved } = useEntityRecords(
		'postType',
		postType,
		{
			per_page: 10,
			order: searchParams.get('order') ?? 'desc',
			orderby: searchParams.get('orderby') ?? 'date',
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
	const handleNew = (args = null) => {
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
		}).then((response) => {
			void createSuccessNotice(
				__('Campaign created', 'kudos-donations'),
				{ type: 'snackbar', icon: <Icon icon="plus" /> }
			);
			return response;
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
			title: post.title.raw,
			date: new Date(),
		};
		handleSave(data).then(() =>
			createSuccessNotice(__('Campaign duplicated', 'kudos-donations'), {
				type: 'snackbar',
				icon: <Icon icon="media-document" />,
			})
		);
	};

	const data = {
		posts: cachedPosts,
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
};

export const useCampaignsContext = () => {
	return useContext(CampaignsContext);
};
