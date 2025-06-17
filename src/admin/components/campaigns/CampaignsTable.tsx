import {
	Button,
	ColorIndicator,
	Flex,
	ProgressBar,
	Tooltip,
	VisuallyHidden,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { Table } from '../table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { useAdminContext, useEntitiesContext } from '../../contexts';
import { useEffect } from '@wordpress/element';
import type { Campaign } from '../../../types/posts';
import { useAdminQueryParams } from '../../hooks';
export const CampaignsTable = ({ handleEdit, handleNew }): React.ReactNode => {
	const { currencies } = window.kudos;
	const { setParams } = useAdminQueryParams();
	const { setHeaderContent } = useAdminContext();
	const { handleDelete, handleDuplicate } = useEntitiesContext();

	useEffect(() => {
		setHeaderContent(
			<Button
				variant="primary"
				onClick={handleNew}
				text={__('New campaign', 'kudos-donations')}
				icon="plus"
			/>
		);
		return () => setHeaderContent(null);
	}, [handleNew, setHeaderContent]);

	const changeView = (postId: number) => {
		void setParams({
			page: 'kudos-transactions',
			column: 'campaign_id',
			value: String(postId),
		});
	};

	const headerItems = [
		{
			key: 'campaign',
			title: __('Campaign', 'kudos-donations'),
			orderby: 'title',
			valueCallback: (post: Campaign): React.ReactNode => (
				<Button
					showTooltip={true}
					style={{
						fontWeight: 'bold',
						color: 'inherit',
					}}
					label={sprintf(
						/* translators: %s is the campaign name */
						__('Edit %s', 'kudos-donations'),
						post.title
					)}
					variant="link"
					onClick={() => handleEdit(post.id)}
				>
					{post.title}
				</Button>
			),
		},
		{
			key: 'theme',
			title: __('Theme color', 'kudos-donations'),
			valueCallback: (post: Campaign): React.ReactNode => (
				<ColorIndicator colorValue={post.theme_color} />
			),
		},
		{
			key: 'progress',
			title: __('Progress', 'kudos-donations'),
			valueCallback: (post: Campaign): React.ReactNode => {
				const total = Number(post.total);
				const goal = Number(post.goal);
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
				`${currencies[post.currency]} ${post.total}`,
		},
		{
			key: 'date',
			title: __('Created', 'kudos-donations'),
			orderby: 'created_at',
			valueCallback: (post: Campaign): React.ReactNode => (
				<i>{dateI18n('d-m-Y', post.created_at, null)}</i>
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
						icon="money-alt"
						onClick={() => changeView(post.id)}
						title={__('View donations', 'kudos-donations')}
					/>
					<Button
						size="compact"
						icon="edit"
						label={__('Edit campaign', 'kudos-donations')}
						onClick={() => handleEdit(post.id)}
					/>
					<Button
						size="compact"
						icon="admin-page"
						label={__('Duplicate campaign', 'kudos-donations')}
						onClick={() => handleDuplicate(post)}
					/>
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
			<Table headerItems={headerItems} />
		</>
	);
};
