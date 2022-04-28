import { useEffect, useState } from '@wordpress/element';
import {
	AlignmentToolbar,
	BlockControls,
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, RadioControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React, { Fragment } from 'react';
import { KudosButton } from '../../public/components/KudosButton';
import { fetchCampaigns } from '../../common/helpers/fetch';

const ButtonEdit = (props) => {
	const [campaigns, setCampaigns] = useState();
	const [currentCampaign, setCurrentCampaign] = useState();

	const {
		className,
		attributes: { button_label, campaign_id, type, alignment },
		setAttributes,
	} = props;

	useEffect(() => {
		fetchCampaigns().then(setCampaigns);
		fetchCampaigns(campaign_id).then(setCurrentCampaign);
	}, []);

	useEffect(() => {
		console.log(currentCampaign);
	}, [currentCampaign]);

	const onChangeButtonLabel = (newValue) => {
		setAttributes({ button_label: newValue });
	};

	const onChangeAlignment = (newValue) => {
		setAttributes({
			alignment: newValue === undefined ? 'none' : newValue,
		});
	};

	const onChangeCampaign = (newValue) => {
		setAttributes({ campaign_id: newValue });
		fetchCampaigns(campaign_id).then(setCurrentCampaign);
	};

	const onChangeType = (newValue) => {
		setAttributes({ type: newValue });
	};

	return (
		<div>
			{campaigns && (
				<Fragment>
					<InspectorControls>
						<PanelBody
							title={__('Campaign', 'kudos-donations')}
							initialOpen={false}
						>
							<p>
								<strong>
									Current campaign:{' '}
									{currentCampaign?.title.rendered}
								</strong>
							</p>
							<SelectControl
								label={__(
									'Select a campaign',
									'kudos-donations'
								)}
								value={campaign_id}
								onChange={onChangeCampaign}
								options={[{ label: '', value: '' }].concat(
									campaigns.map((campaign) => ({
										label: campaign?.title.rendered,
										value: campaign.id,
									}))
								)}
							/>
							<a href="admin.php?page=kudos-campaigns&tab_name=campaigns">
								{__(
									'Create a new campaign here',
									'kudos-donations'
								)}
							</a>
						</PanelBody>

						<PanelBody
							title={__('Options', 'kudos-donations')}
							initialOpen={false}
						>
							<RadioControl
								label={__('Display type', 'kudos-donations')}
								selected={type}
								options={[
									{
										label: __('Button', 'kudos-donations'),
										value: 'button',
									},
									{
										label: __('Form', 'kudos-donations'),
										value: 'form',
									},
								]}
								onChange={onChangeType}
							/>
						</PanelBody>
					</InspectorControls>

					<BlockControls>
						<AlignmentToolbar
							value={alignment}
							onChange={onChangeAlignment}
						/>
					</BlockControls>

					<KudosButton
						color={currentCampaign?.meta.theme_color}
						className={
							(className ?? '') + ' has-text-align-' + alignment
						}
					>
						<RichText
							allowedFormats={[
								'core/bold',
								'core/italic',
								'core/text-color',
								'core/strikethrough',
							]}
							onChange={onChangeButtonLabel}
							value={button_label}
						/>
					</KudosButton>
				</Fragment>
			)}
		</div>
	);
};

export default function Edit(props) {
	return (
		<div {...useBlockProps()}>
			<ButtonEdit {...props} />
		</div>
	);
}
