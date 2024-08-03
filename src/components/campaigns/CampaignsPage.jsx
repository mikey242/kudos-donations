// eslint-disable-next-line import/default
import { Header } from '../admin/Header';
import React from 'react';
import CampaignEdit from './CampaignEdit';
import { __ } from '@wordpress/i18n';
import { Button } from '../controls';
import EmptyCampaigns from './EmptyCampaigns';
import { Spinner } from '../Spinner';
import { useSettingsContext } from '../../contexts/SettingsContext';
import {
	ArrowDownTrayIcon,
	DocumentDuplicateIcon,
	PencilSquareIcon,
	PlusCircleIcon,
	PlusIcon,
	TrashIcon,
} from '@heroicons/react/24/outline';
import { useAdminTableContext } from '../../contexts/AdminTableContext';
import { InlineTextEdit } from '../controls/InlineTextEdit';
import { ColorPickerPopup } from './ColorPickerPopup';
import { ProgressBar } from '../ProgressBar';
import { dateI18n } from '@wordpress/date';
import { Fragment } from '@wordpress/element';
import Table from '../admin/Table';

const CampaignsPage = () => {
	const {
		updatePost,
		posts,
		newPost,
		isApiBusy,
		currentPost,
		setPostId,
		duplicatePost,
		deletePost,
	} = useAdminTableContext();
	const { settings, settingsReady } = useSettingsContext();
	const { currencies } = window.kudos;

	const newCampaign = () => {
		newPost(__('New campaign', 'kudos-donations')).then((response) => {
			setPostId(response.id);
		});
	};

	const headerItems = [
		{
			title: __('Campaign name', 'kudos-donations'),
			orderby: 'title',
			dataCallback: () => <InlineTextEdit name={'title'} />,
		},
		{
			title: __('Color', 'kudos-donations'),
			dataCallback: (i, formRef) => (
				<ColorPickerPopup
					color={posts[i].meta?.theme_color}
					onColorChange={() => formRef?.current.requestSubmit()}
				/>
			),
		},
		{
			title: __('Goal', 'kudos-donations'),
			headerClass: 'w-20',
			dataCallback: (i) =>
				`${currencies[posts[i].meta?.currency]} ${posts[i].meta?.goal}`,
		},
		{
			title: __('Progress', 'kudos-donations'),
			headerClass: 'min-w-[13rem]',
			dataCallback: (i) => {
				return posts[i].meta?.goal > 0 ? (
					<ProgressBar
						goal={posts[i].meta?.goal}
						total={posts[i].total}
						currency={currencies[posts[i].meta?.currency]}
						showGoal={false}
					/>
				) : (
					`${currencies[posts[i].meta?.currency] ?? ''} ${posts[i].total}`
				);
			},
		},
		{
			title: __('Created', 'kudos-donations'),
			orderby: 'date',
			dataCallback: (i) => (
				<i className="text-gray-500">
					{dateI18n('d-m-Y', posts[i].date, null)}
				</i>
			),
		},
		{
			title: (
				<span className="sr-only">{__('Edit', 'kudos-donations')}</span>
			),
			dataCallback: (i) => (
				<Fragment>
					<span title={__('Edit campaign', 'kudos-donations')}>
						<PencilSquareIcon
							className="h-5 w-5 cursor-pointer mx-1 font-medium inline-block"
							onClick={() => setPostId(posts[i].id)}
						/>
					</span>
					<span title={__('Duplicate campaign', 'kudos-donations')}>
						<DocumentDuplicateIcon
							className="h-5 w-5 cursor-pointer mx-1 font-medium inline-block"
							onClick={() => duplicatePost(posts[i])}
						/>
					</span>
					<span title={__('Delete campaign', 'kudos-donations')}>
						<TrashIcon
							className="h-5 w-5 cursor-pointer mx-1 font-medium inline-block text-red-500"
							onClick={() => {
								return (
									// eslint-disable-next-line no-alert
									window.confirm(
										__(
											'Are you sure you wish to delete this campaign?',
											'kudos-donations'
										)
									) && deletePost(posts[i].id)
								);
							}}
						/>
					</span>
				</Fragment>
			),
		},
	];

	return (
		<>
			{!posts && !settingsReady ? (
				<div className="absolute inset-0 flex items-center justify-center">
					<Spinner />
				</div>
			) : (
				<>
					<Header>
						{currentPost && (
							<Button
								form="settings-form"
								type="submit"
								isBusy={isApiBusy}
								icon={
									currentPost.status === 'draft' ? (
										<PlusCircleIcon className="mr-2 w-5 h-5" />
									) : (
										<ArrowDownTrayIcon className="mr-2 w-5 h-5" />
									)
								}
							>
								{__('Save', 'kudos-donations')}
							</Button>
						)}
					</Header>
					{!currentPost ? (
						<div className="max-w-5xl w-full mx-auto">
							{posts?.length >= 1 ? (
								<Table
									headerItems={headerItems}
									tableData={posts}
									updatePost={updatePost}
								/>
							) : (
								<EmptyCampaigns />
							)}
							<button
								title={__('Add campaign', 'kudos-donations')}
								className="rounded-full mx-auto p-2 flex justify-center items-center bg-white mt-5 shadow-md border-0 cursor-pointer"
								onClick={newCampaign}
							>
								<PlusIcon className={'w-5 h-5'} />
							</button>
						</div>
					) : (
						<div className="max-w-4xl w-full mx-auto">
							<CampaignEdit
								updateCampaign={updatePost}
								recurringAllowed={
									settings?.[
										'_kudos_vendor_' +
											settings._kudos_vendor
									]?.recurring
								}
								campaign={currentPost}
							/>
						</div>
					)}
				</>
			)}
		</>
	);
};

export { CampaignsPage };
