import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	Button,
	ColorIndicator,
	Flex,
	ProgressBar,
	Tooltip,
	VisuallyHidden,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { Table } from '../Table';
import React from 'react';
import { dateI18n } from '@wordpress/date';
import { useCampaignsContext } from '../../contexts/CampaignsContext';

export const CampaignsTable = ({ handleEdit }) => {
	const { currencies } = window.kudos;
	const { handleNew, handleDelete, handleDuplicate } = useCampaignsContext();

	const headerItems = [
		{
			title: __('Campaign', 'kudos-donations'),
			orderby: 'title',
			valueCallback: (post) => (
				<Button
					showTooltip={true}
					style={{ textDecoration: 'none' }}
					label={sprintf(
						/* translators: %s is the campaign name */
						__('Edit %s', 'kudos-donations'),
						post.title.rendered
					)}
					variant="link"
					onClick={() => handleEdit('edit', post.id)}
				>
					{post.title.rendered}
				</Button>
			),
		},
		{
			title: __('Theme color', 'kudos-donations'),
			valueCallback: (post) => (
				<ColorIndicator colorValue={post.meta?.theme_color} />
			),
		},
		{
			title: __('Total / Goal', 'kudos-donations'),
			valueCallback: (post) =>
				`${currencies[post.meta?.currency]} ${post.total} / ${post.meta?.goal}`,
		},
		{
			title: __('Progress', 'kudos-donations'),
			valueCallback: (post) => {
				const total = post.total;
				const goal = post.meta.goal;
				const progress =
					total && goal ? Math.round((total / goal) * 100) : 0;

				return (
					<Tooltip text={`${progress}%`}>
						<ProgressBar
							className="kudos-campaign-progress"
							value={progress ?? 0}
							currency={currencies[post.meta?.currency]}
							showGoal={false}
						/>
					</Tooltip>
				);
			},
		},
		{
			title: __('Created', 'kudos-donations'),
			orderby: 'date',
			valueCallback: (post) => (
				<i>{dateI18n('d-m-Y', post.date, null)}</i>
			),
		},
		{
			title: (
				<VisuallyHidden>{__('Edit', 'kudos-donations')}</VisuallyHidden>
			),
			valueCallback: (post) => (
				<>
					<Button
						size="compact"
						icon="edit"
						label={__('Edit campaign', 'kudos-donations')}
						onClick={() => handleEdit('edit', post.id)}
					/>
					<Button
						size="compact"
						icon="media-document"
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
				</>
			),
		},
	];

	return (
		<>
			<Flex justify="center">
				<h1>{__('Your campaigns', 'kudos-donations')}</h1>
			</Flex>
			<Table headerItems={headerItems} />
			<Spacer marginTop={'5'} />
			<Flex justify="center">
				<Button
					variant="secondary"
					onClick={handleNew}
					text={__('New campaign', 'kudos-donations')}
					icon="plus"
				/>
			</Flex>
		</>
	);
};
