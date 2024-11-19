/* eslint camelcase: 0 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	ExternalLink,
	Flex,
	PanelBody,
	RadioControl,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { useCampaignContext } from './contexts/CampaignContext';
import { useEntityRecords } from '@wordpress/core-data';
import { KudosForm } from './components/KudosForm';
import { KudosLogo } from './components/KudosLogo';

const ButtonEdit = (props) => {
	const {
		attributes: { button_label, type },
		setAttributes,
	} = props;
	const { campaign, isLoading: campaignLoaded } = useCampaignContext();
	const blockProps = useBlockProps();
	const { records: campaigns, hasResolved: campaignsLoaded } =
		useEntityRecords('postType', 'kudos_campaign', {
			per_page: -1,
		});

	const onChangeButtonLabel = (newValue) => {
		setAttributes({ button_label: newValue });
	};

	const onChangeCampaign = (newValue) => {
		if (newValue) {
			setAttributes({ campaign_id: newValue });
		}
	};

	const onChangeType = (newValue) => {
		setAttributes({ type: newValue });
	};

	const CampaignSelector = () => {
		return (
			<SelectControl
				label={__('Select Campaign', 'kudos-donations')}
				value={campaign?.id ?? ''}
				onChange={onChangeCampaign}
				options={
					campaigns
						?.map((item) => ({
							label: item?.title.rendered,
							value: item.id,
						}))
						.concat({
							label: __('None', 'kudos-donations'),
							value: '',
							disabled: true,
						}) || []
				}
				__nextHasNoMarginBottom
			/>
		);
	};

	return (
		<div {...blockProps}>
			{campaignsLoaded && (
				<InspectorControls>
					<PanelBody
						title={__('Campaign Settings', 'kudos-donations')}
						initialOpen={true}
					>
						<CampaignSelector />
						{campaign?.length > 0 ? (
							<ExternalLink
								href={`admin.php?page=kudos-campaigns&edit=${campaign?.id}`}
							>
								{__('Edit', 'kudos-donations') +
									' ' +
									campaign?.title?.rendered}
							</ExternalLink>
						) : (
							<ExternalLink href="admin.php?page=kudos-campaigns">
								{__('Create a campaign', 'kudos-donations')}
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
			{campaign ? (
				<KudosForm
					displayAs={type}
					label={button_label}
					previewMode={true}
				/>
			) : (
				!campaignLoaded && (
					<Flex justify="flex-start">
						<KudosLogo style={{ maxWidth: '32px' }} />
						<p>
							{__(
								'Please select a campaign from the sidebar',
								'kudos-donations'
							)}
						</p>
					</Flex>
				)
			)}
		</div>
	);
};

export default ButtonEdit;
