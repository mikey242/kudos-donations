import React from 'react';
import { __ } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import {
	DocumentDuplicateIcon,
	PencilSquareIcon,
	TrashIcon,
} from '@heroicons/react/24/outline';
import { InlineTextEdit } from '../controls/InlineTextEdit';
import { FormProvider, useForm } from 'react-hook-form';
import { useRef } from '@wordpress/element';
import { ColorPickerPopup } from './ColorPickerPopup';
import { ProgressBar } from '../ProgressBar';

const CampaignsTableRow = ({
	campaign,
	editClick,
	duplicateClick,
	deleteClick,
	updateCampaign,
}) => {
	const formRef = useRef(null);
	const methods = useForm({
		defaultValues: {
			...campaign,
			title: campaign?.title?.rendered,
			'shortcode.showAs': 'button',
			'shortcode.buttonLabel': __('Donate now!', 'kudos-donations'),
		},
	});

	const { handleSubmit } = methods;

	const date = dateI18n('d-m-Y', campaign.date);

	const save = (data) => {
		updateCampaign(data.id, data, false);
	};

	return (
		<FormProvider {...methods}>
			<form
				className="table-row text-sm"
				onSubmit={handleSubmit(save)}
				key={campaign.id}
				ref={formRef}
			>
				<div className="table-cell align-middle whitespace-nowrap px-3 py-4 sm:pl-6 text-gray-700">
					<InlineTextEdit name={'title'} />
				</div>
				<div className="table-cell align-middle whitespace-nowrap px-3 py-4">
					<ColorPickerPopup
						color={campaign.meta.theme_color}
						onColorChange={() => formRef.current.requestSubmit()}
					/>
				</div>
				<div className="w-20 table-cell align-middle whitespace-nowrap  px-3 py-4 text-gray-700">
					<InlineTextEdit
						type="number"
						className="w-full [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
						validation={{
							min: {
								value: 1,
								message: __(
									'Minimum value is 1',
									'kudos-donations'
								),
							},
						}}
						name={'meta.goal'}
					/>
				</div>
				<div className="table-cell align-middle whitespace-nowrap px-3 py-4 text-gray-900">
					<div className="block w-60">
						<ProgressBar
							percentage={Math.round(campaign.progress)}
							total={campaign.total}
							showGoal={false}
						/>
					</div>
				</div>
				<div className="table-cell align-middle whitespace-nowrap px-3 py-4 text-gray-500">
					<p>{date}</p>
				</div>
				<div className="relative table-cell align-middle whitespace-nowrap py-4 pl-3 pr-4 divide-x-8 divide-transparent text-right font-medium sm:pr-6">
					<span title={__('Edit campaign', 'kudos-donations')}>
						<PencilSquareIcon
							className="h-5 w-5 cursor-pointer mx-1 font-medium inline-block"
							onClick={() => editClick(campaign)}
						/>
					</span>
					<span title={__('Duplicate campaign', 'kudos-donations')}>
						<DocumentDuplicateIcon
							className="h-5 w-5 cursor-pointer mx-1 font-medium inline-block"
							onClick={() => duplicateClick(campaign)}
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
									) && deleteClick(campaign.id)
								);
							}}
						/>
					</span>
				</div>
			</form>
		</FormProvider>
	);
};

export default CampaignsTableRow;
