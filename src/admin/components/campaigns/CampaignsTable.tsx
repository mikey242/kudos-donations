import {
	Button,
	ColorIndicator,
	Flex,
	ProgressBar,
	Tooltip,
	VisuallyHidden,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { Table } from '../table/Table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { useAdminContext, usePostsContext } from '../contexts';
import GenerateShortcode from './GenerateShortcode';
import { useEffect } from '@wordpress/element';
import type { Campaign } from '../../../types/posts';

export const CampaignsTable = ({ handleEdit }): React.ReactNode => {
	const { currencies } = window.kudos;
	const { setPageTitle } = useAdminContext();
	const {
		handleDelete,
		handleDuplicate,
		isLoading,
		hasLoadedOnce,
		posts,
		totalPages,
		totalItems,
	} = usePostsContext();

	useEffect(() => {
		setPageTitle(__('Your campaigns', 'kudos-donations'));
	}, [setPageTitle]);

	const headerItems = [
		{
			key: 'campaign',
			title: __('Campaign', 'kudos-donations'),
			orderby: 'title',
			valueCallback: (post: Campaign): React.ReactNode => (
				<Button
					showTooltip={true}
					style={{ textDecoration: 'none', color: 'inherit' }}
					label={sprintf(
						/* translators: %s is the campaign name */
						__('Edit %s', 'kudos-donations'),
						post.title.raw
					)}
					variant="link"
					onClick={() => handleEdit('edit', post.id)}
				>
					{post.title.raw}
				</Button>
			),
		},
		{
			key: 'theme',
			title: __('Theme color', 'kudos-donations'),
			valueCallback: (post: Campaign): React.ReactNode => (
				<ColorIndicator colorValue={post.meta?.theme_color} />
			),
		},
		{
			key: 'progress',
			title: __('Progress', 'kudos-donations'),
			valueCallback: (post: Campaign): React.ReactNode => {
				const total = post.total;
				const goal = post.meta.goal;
				const progress =
					total && goal ? Math.round((total / goal) * 100) : 0;

				return (
					<Tooltip text={`${progress}%`}>
						<ProgressBar
							className="kudos-campaign-progress"
							value={progress ?? 0}
						/>
					</Tooltip>
				);
			},
		},
		{
			key: 'total',
			title: __('Total', 'kudos-donations'),
			valueCallback: (post: Campaign): React.ReactNode =>
				`${currencies[post.meta?.currency]} ${post.total}`,
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'date',
			valueCallback: (post: Campaign): React.ReactNode => (
				<i>{dateI18n('d-m-Y', post.date, null)}</i>
			),
		},
		{
			key: 'edit',
			title: (
				<VisuallyHidden>{__('Edit', 'kudos-donations')}</VisuallyHidden>
			),
			valueCallback: (post: Campaign): React.ReactNode => (
				<Flex justify="flex-end">
					<Button
						size="compact"
						icon="edit"
						label={__('Edit campaign', 'kudos-donations')}
						onClick={() => handleEdit('edit', post.id)}
					/>
					<Button
						size="compact"
						icon="admin-page"
						label={__('Duplicate campaign', 'kudos-donations')}
						onClick={() => handleDuplicate(post)}
					/>
					<GenerateShortcode campaign={post} iconOnly />
					<Button
						size="compact"
						icon="trash"
						label={__('Delete campaign', 'kudos-donations')}
						onClick={() => {
							return (
								// eslint-disable-next-line no-alert
								window.confirm(
									__(
										'Are you sure you wish to delete this campaign?',
										'kudos-donations'
									)
								) && handleDelete(post.id)
							);
						}}
					/>
				</Flex>
			),
		},
	];

	return (
		<>
			<Table
				posts={posts}
				headerItems={headerItems}
				isLoading={isLoading}
				hasLoadedOnce={hasLoadedOnce}
				totalPages={totalPages}
				totalItems={totalItems}
			/>
		</>
	);
};
