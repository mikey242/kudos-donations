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
import type { Campaign } from '../../../types/wp';
import apiFetch from '@wordpress/api-fetch';

interface CampaignsContextValue {
	posts: Campaign[];
	hasResolved: boolean;
	isLoading: boolean;
	hasLoadedOnce: boolean;
	handleNew: (
		args?: Partial<Campaign> | React.SyntheticEvent
	) => Promise<any>;
	handleUpdate: (data: Partial<Campaign>) => Promise<any>;
	handleDelete: (postId: number) => void;
	handleDuplicate: (post: Campaign) => void;
	recurringEnabled: boolean;
}

const CampaignsContext = createContext<CampaignsContextValue | null>(null);

export const CampaignsProvider = ({
	postType = 'kudos_campaign',
	children,
}) => {
	const { searchParams } = useAdminContext();
	const { saveEntityRecord, deleteEntityRecord } = useDispatch('core');
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);
	const [cachedPosts, setCachedPosts] = useState<Campaign[]>([]);
	const [isLoading, setIsLoading] = useState<boolean>(true);
	const [hasLoadedOnce, setHasLoadedOnce] = useState<boolean>(false);
	const [recurringEnabled, setRecurringEnabled] = useState<boolean>(false);
	const { records: posts, hasResolved } = useEntityRecords<Campaign>(
		'postType',
		postType,
		{
			per_page: -1,
			order: searchParams.get('order') ?? 'desc',
			orderby: searchParams.get('orderby') ?? 'date',
		}
	);

	useEffect(() => {
		if (hasResolved) {
			setCachedPosts(posts);
			setIsLoading(false);
			setHasLoadedOnce(true);
		}
	}, [posts, hasResolved]);

	useEffect(() => {
		apiFetch({
			path: '/kudos/v1/payment/recurring-enabled',
			method: 'GET',
		}).then((r: boolean) => setRecurringEnabled(r));
	}, []);

	const handleSave = useCallback(
		async (args = {}): Promise<any | null> => {
			try {
				const response = await saveEntityRecord(
					'postType',
					'kudos_campaign',
					args
				);

				if (!response) {
					throw new Error('No response from API.');
				}
				return response;
			} catch (error: any) {
				void createErrorNotice(
					sprintf(
						/* translators: %s is the error message. */
						__('Error creating campaign: %s', 'kudos-donations'),
						error.message
					)
				);
				return null;
			}
		},
		[createErrorNotice, saveEntityRecord]
	);

	const handleUpdate = async (data: Partial<Campaign>) => {
		const response = await handleSave(data);
		void createSuccessNotice(__('Campaign updated', 'kudos-donations'), {
			type: 'snackbar',
			icon: <Icon icon="saved" />,
		});
		return response;
	};

	// Handles creating a campaign.
	const handleNew = async (
		args?: Partial<Campaign> | React.SyntheticEvent
	): Promise<any> => {
		// If args is a SyntheticEvent, ignore it and create an empty args object
		if (
			args &&
			typeof (args as React.SyntheticEvent).preventDefault === 'function'
		) {
			args = {};
		}

		// Set default arguments.
		const {
			title = __('New Campaign', 'kudos-donations'),
			status = 'publish',
			...rest
		} = (args as Partial<Campaign>) || {};

		const response = await handleSave({
			title,
			status,
			...rest,
		});

		if (response) {
			void createSuccessNotice(
				__('Campaign created', 'kudos-donations'),
				{
					type: 'snackbar',
					icon: <Icon icon="plus" />,
				}
			);
		}
		return response;
	};

	const handleDelete = useCallback(
		async (postId: number): Promise<void> => {
			try {
				await deleteEntityRecord('postType', 'kudos_campaign', postId, {
					force: true,
				});
				await createSuccessNotice(
					__('Campaign deleted', 'kudos-donations'),
					{
						type: 'snackbar',
						icon: <Icon icon="trash" />,
					}
				);
			} catch (error: any) {
				await createErrorNotice(
					sprintf(
						/* translators: %s is the error message. */
						__('Error deleting campaign: %s', 'kudos-donations'),
						error?.message ?? 'Unknown error'
					),
					{ type: 'snackbar' }
				);
			}
		},
		[createSuccessNotice, createErrorNotice, deleteEntityRecord]
	);

	// Prepares data for duplicating current post.
	const handleDuplicate = (post: Campaign) => {
		const { id, ...rest } = post;

		const data: Partial<Campaign> = {
			...rest,
			title: { raw: post.title.raw, rendered: '' },
			date: new Date().toISOString(),
		};
		return handleSave(data);
	};

	const data = {
		posts: cachedPosts,
		hasResolved,
		isLoading,
		hasLoadedOnce,
		handleNew,
		handleDuplicate,
		handleDelete,
		handleUpdate,
		recurringEnabled,
	};

	return (
		<>
			<CampaignsContext.Provider value={data}>
				{children}
			</CampaignsContext.Provider>
		</>
	);
};

export const useCampaignsContext = (): CampaignsContextValue => {
	const context = useContext(CampaignsContext);
	if (!context) {
		throw new Error(
			'useCampaignsContext must be used within a CampaignsProvider'
		);
	}
	return context;
};
