/* eslint camelcase: 0 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	ExternalLink,
	PanelBody,
	RadioControl,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { useCampaignContext } from './contexts/CampaignContext';
import { useEntityRecords } from '@wordpress/core-data';
import { useEffect } from '@wordpress/element';
import { KudosForm } from './components/KudosForm';

const ButtonEdit = (props) => {
	const {
		attributes: { button_label, campaign_id, type },
		setAttributes,
	} = props;
	const { campaign: currentCampaign } = useCampaignContext();
	const blockProps = useBlockProps();
	const { records: campaigns } = useEntityRecords(
		'postType',
		'kudos_campaign',
		{
			per_page: -1,
		}
	);

	const onChangeButtonLabel = (newValue) => {
		setAttributes({ button_label: newValue });
	};

	const onChangeCampaign = (newValue) => {
		if (newValue) {
			setAttributes({ campaign_id: String(newValue) });
		}
	};

	const onChangeType = (newValue) => {
		setAttributes({ type: newValue });
	};

	useEffect(() => {
		if (!campaign_id && campaigns?.length > 0) {
			setAttributes({ campaign_id: String(campaigns[0].id) });
		}
	}, [campaign_id, campaigns, setAttributes]);

	return (
		<div {...blockProps}>
			{campaigns && (
				<InspectorControls>
					<PanelBody
						title={__('Campaign Settings', 'kudos-donations')}
						initialOpen={true}
					>
						<SelectControl
							label={__('Select Campaign', 'kudos-donations')}
							value={campaign_id}
							onChange={onChangeCampaign}
							options={
								campaigns?.map((item) => ({
									label: item?.title.rendered,
									value: item.id,
								})) || []
							}
							__nextHasNoMarginBottom
						/>
						{currentCampaign && (
							<ExternalLink
								href={`admin.php?page=kudos-campaigns&edit=${currentCampaign?.id}`}
							>
								{__('Edit', 'kudos-donations') +
									' ' +
									currentCampaign?.title?.rendered}
							</ExternalLink>
						)}
					</PanelBody>
					<PanelBody
						title={__('Appearance', 'kudos-donations')}
						initialOpen={false}
					>
						<RadioControl
							label={__('Display Type', 'kudos-donations')}
							selected={type}
							options={[
								{
									label: __(
										'Embedded form',
										'kudos-donations'
									),
									value: 'form',
								},
								{
									label: __(
										'Button with modal',
										'kudos-donations'
									),
									value: 'button',
								},
							]}
							onChange={onChangeType}
						/>
						{type === 'button' && (
							<TextControl
								label={__('Button Label', 'kudos-donations')}
								value={button_label}
								onChange={onChangeButtonLabel}
								__nextHasNoMarginBottom
							/>
						)}
					</PanelBody>
				</InspectorControls>
			)}
			{currentCampaign ? (
				<KudosForm
					displayAs={type}
					label={button_label}
					previewMode={true}
				/>
			) : (
				<p>
					Kudos Donations:{' '}
					{__(
						'Please select a campaign from the sidebar to continue.',
						'kudos-donations'
					)}
				</p>
			)}
		</div>
	);
};

export default ButtonEdit;
