import React from 'react';
import {
	createContext,
	useCallback,
	useContext,
	useEffect,
	useState,
} from '@wordpress/element';
// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import {
	NumberParam,
	StringParam,
	useQueryParam,
	useQueryParams,
	withDefault,
} from 'use-query-params';
import { useNotificationContext } from './NotificationContext';
import { removeQueryParameters } from '../helpers/util';

export const AdminTableContext = createContext(null);

export default function AdminTableProvider({
	postType = 'post',
	singular,
	children,
}) {
	const { createNotification } = useNotificationContext();
	const [posts, setPosts] = useState(null);
	const [isApiBusy, setIsApiBusy] = useState(false);
	const [currentPost, setCurrentPost] = useState(null);
	const [postId, setPostId] = useQueryParam(postType, NumberParam);
	const [sortQuery] = useQueryParams({
		order: withDefault(StringParam, 'desc'),
		orderby: withDefault(StringParam, 'date'),
	});

	const clearCurrentPost = useCallback(() => {
		removeQueryParameters([postType, 'tab']);
		setCurrentPost(null);
	}, [postType]);

	const getPosts = useCallback(() => {
		setIsApiBusy(true);
		apiFetch({
			path: `wp/v2/${postType}?orderby=${sortQuery?.orderby}&order=${sortQuery?.order}`,
			method: 'GET',
		})
			.then(setPosts)
			.catch((error) => {
				createNotification(error.message);
			})
			.finally(() => setIsApiBusy(false));
	}, [createNotification, postType, sortQuery]);

	const newPost = (title) => {
		return updatePost(null, {
			title,
			status: 'draft',
		});
	};

	const updatePost = (id = null, data = {}, notification = true) => {
		setIsApiBusy(true);
		return apiFetch({
			path: `wp/v2/${postType}/${id ?? ''}`,
			method: 'POST',
			data: {
				...data,
				status: 'publish',
			},
		})
			.then((response) => {
				getPosts();
				if (notification) {
					createNotification(
						data.status === 'draft'
							? singular + ' ' + __('created', 'kudos-donations')
							: singular + ' ' + __('updated', 'kudos-donations'),
						true
					);
				}
				return response;
			})
			.catch((error) => {
				createNotification(error.message, false);
			})
			.finally(() => {
				setIsApiBusy(false);
			});
	};
	const deletePost = (id) => {
		return apiFetch({
			path: `wp/v2/kudos_campaign/${id}?force=true`,
			method: 'DELETE',
		}).then(() => {
			createNotification(
				singular + ' ' + __('deleted', 'kudos-donations'),
				true
			);
			return getPosts();
		});
	};

	const duplicatePost = (post) => {
		const data = {
			...post,
			id: null,
			title: post.title.rendered,
			date: new Date(),
			status: 'draft',
		};
		return updatePost(null, data);
	};

	useEffect(() => {
		getPosts();
	}, [getPosts]);

	useEffect(() => {
		if (posts) {
			if (postId) {
				setCurrentPost(posts.filter((c) => c.id === postId)[0]);
			} else {
				clearCurrentPost();
			}
		}
	}, [clearCurrentPost, postId, posts]);

	const data = {
		posts,
		setPostId,
		currentPost,
		newPost,
		updatePost,
		deletePost,
		duplicatePost,
		isApiBusy,
		clearCurrentPost,
	};

	return (
		<>
			<AdminTableContext.Provider value={data}>
				{children}
			</AdminTableContext.Provider>
		</>
	);
}

export const useAdminTableContext = () => {
	return useContext(AdminTableContext);
};
