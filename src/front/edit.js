/* eslint camelcase: 0 */

import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	ExternalLink,
	Flex,
	FlexItem,
	PanelBody,
	RadioControl,
	SelectControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
// eslint-disable-next-line import/default
import { DonateButton } from './components/DonateButton';
import { FormRouter } from './components/FormRouter';
import Render from './components/Render';
import { useCampaignContext } from './contexts/CampaignContext';
import { useEntityRecords } from '@wordpress/core-data';
import { useEffect } from '@wordpress/element';

const ButtonEdit = (props) => {
	const {
		attributes: { button_label, campaign_id, type, className },
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
		if (campaigns?.length > 0) {
			if (!campaign_id) {
				setAttributes({ campaign_id: String(campaigns[0].id) });
			}
		}
	}, [campaign_id, campaigns, setAttributes]);

	return (
		<div {...blockProps}>
			{campaigns && (
				<>
					<InspectorControls>
						<PanelBody
							title={__('Campaign', 'kudos-donations')}
							initialOpen={false}
						>
							<SelectControl
								label={__(
									'Select a campaign',
									'kudos-donations'
								)}
								value={campaign_id}
								onChange={onChangeCampaign}
								options={[{ label: '', value: '' }].concat(
									campaigns?.map((item) => ({
										label: item?.title.rendered,
										value: item.id,
									}))
								)}
							/>
							<Flex>
								<FlexItem>
									{currentCampaign && (
										<>
											<ExternalLink
												href={`admin.php?page=kudos-campaigns&edit=${currentCampaign?.id}`}
											>
												{__('Edit', 'kudos-donations') +
													' ' +
													currentCampaign?.title
														?.rendered}
											</ExternalLink>
											<br />
										</>
									)}
									<ExternalLink href="admin.php?page=kudos-campaigns&tab_name=campaigns">
										{__(
											'Create a new campaign',
											'kudos-donations'
										)}
									</ExternalLink>
								</FlexItem>
							</Flex>
						</PanelBody>

						<PanelBody
							title={__('Format', 'kudos-donations')}
							initialOpen={false}
						>
							<RadioControl
								label={__('Display as', 'kudos-donations')}
								selected={type}
								options={[
									{
										label: __(
											'Button with modal',
											'kudos-donations'
										),
										value: 'button',
									},
									{
										label: __(
											'Embedded form',
											'kudos-donations'
										),
										value: 'form',
									},
								]}
								onChange={onChangeType}
							/>
						</PanelBody>
					</InspectorControls>
					<>
						{currentCampaign ? (
							<Render
								className={className ?? ''}
								themeColor={currentCampaign?.meta?.theme_color}
							>
								{type === 'form' ? (
									<FormRouter
										isPreview={true}
										campaign={currentCampaign}
									/>
								) : (
									<DonateButton>
										<RichText
											allowedFormats={[]} // Disable all formatting
											onChange={onChangeButtonLabel}
											value={button_label}
										/>
									</DonateButton>
								)}
							</Render>
						) : (
							<p>
								Kudos Donations:{' '}
								{__(
									'Please select a campaign from the sidebar to continue.'
								)}
							</p>
						)}
					</>
				</>
			)}
		</div>
	);
};

export default ButtonEdit;
