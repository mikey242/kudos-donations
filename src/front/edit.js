/* eslint camelcase: 0 */

import { useEffect, useState } from '@wordpress/element';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	ExternalLink,
	Flex,
	FlexItem,
	Icon,
	PanelBody,
	RadioControl,
	SelectControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React from 'react';
// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { DonateButton } from './components/DonateButton';
import { FormRouter } from './components/FormRouter';
import Render from './components/Render';

const ButtonEdit = (props) => {
	const [campaigns, setCampaigns] = useState(null);
	const [currentCampaign, setCurrentCampaign] = useState(null);
	const [updatingCampaigns, setUpdatingCampaigns] = useState(false);

	const {
		attributes: { button_label, campaign_id, type, className },
		setAttributes,
	} = props;

	const blockProps = useBlockProps();

	const fetchCampaigns = async () => {
		setUpdatingCampaigns(true);
		// Fetch data from the API
		return await apiFetch({
			path: `wp/v2/kudos_campaign/`,
			method: 'GET',
		})
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.log(error);
			})
			.finally(() => {
				setUpdatingCampaigns(false);
			}); // Return fetched data
	};

	useEffect(() => {
		let isMounted = true; // Track whether the component is still mounted

		fetchCampaigns().then((data) => {
			if (isMounted) {
				setCampaigns(data);
			}
		});

		// Cleanup function to prevent state updates if component unmounts
		return () => {
			isMounted = false;
		};
	}, [campaign_id]);

	useEffect(() => {
		if (campaigns?.length > 0) {
			if (campaign_id) {
				const current = campaigns.find(
					(x) => x.id === parseInt(campaign_id)
				);
				setCurrentCampaign(current);
			} else {
				setAttributes({ campaign_id: String(campaigns[0].id) });
			}
		}
	}, [campaign_id, campaigns, setAttributes]);

	const onChangeButtonLabel = (newValue) => {
		setAttributes({ button_label: newValue });
	};

	const onChangeCampaign = (newValue) => {
		if (newValue) {
			setAttributes({ campaign_id: String(newValue) });
			apiFetch({
				path: `wp/v2/kudos_campaign/${newValue ?? ''}`,
				method: 'GET',
			}).then(setCurrentCampaign);
		}
	};

	const onChangeType = (newValue) => {
		setAttributes({ type: newValue });
	};

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
									campaigns?.map((campaign) => ({
										label: campaign?.title.rendered,
										value: campaign.id,
									}))
								)}
							/>
							<Flex>
								<FlexItem>
									{currentCampaign && (
										<>
											<ExternalLink
												href={`admin.php?page=kudos-campaigns&edit=${currentCampaign.id}`}
											>
												{__('Edit', 'kudos-donations') +
													' ' +
													currentCampaign.title
														.rendered}
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
								<FlexItem>
									<Button
										type="button"
										icon={<Icon icon="update" />}
										isBusy={updatingCampaigns}
										label={__(
											'Refresh campaign(s)',
											'kudos-donations'
										)}
										onClick={() => {
											fetchCampaigns().then((data) => {
												setCampaigns(data);
											});
										}}
									/>
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
