import { useEffect, useState } from '@wordpress/element';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, RadioControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import React, { Fragment } from 'react';
// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { DonateButton } from '../../components/DonateButton';

const ButtonEdit = (props) => {
	const [campaigns, setCampaigns] = useState(null);
	const [currentCampaign, setCurrentCampaign] = useState(null);

	const {
		className,
		// eslint-disable-next-line camelcase
		attributes: { button_label, campaign_id, type },
		setAttributes,
	} = props;

	useEffect(() => {
		const controller =
			typeof AbortController === 'undefined'
				? undefined
				: new AbortController();
		apiFetch({
			path: `wp/v2/kudos_campaign/`,
			method: 'GET',
			signal: controller?.signal,
		})
			.then((response) => {
				setCampaigns(response);
				// eslint-disable-next-line camelcase
				if (campaign_id) {
					const current = response.find(
						(x) => x.id === parseInt(campaign_id)
					);
					setCurrentCampaign(current);
				}
			})
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.log(error);
			});

		return () => {
			controller.abort();
		};
		// eslint-disable-next-line camelcase
	}, [campaign_id]);

	const onChangeButtonLabel = (newValue) => {
		setAttributes({ button_label: newValue });
	};

	const onChangeCampaign = (newValue) => {
		if (newValue) {
			setAttributes({ campaign_id: newValue });
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
		<div>
			{campaigns && (
				<Fragment>
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
								// eslint-disable-next-line camelcase
								value={campaign_id}
								onChange={onChangeCampaign}
								options={[{ label: '', value: '' }].concat(
									campaigns?.map((campaign) => ({
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

					<DonateButton
						className={className ?? ''}
						// eslint-disable-next-line camelcase
						color={currentCampaign?.meta.theme_color ?? '#ff9f1c'}
					>
						<RichText
							allowedFormats={[]} // Disable all formatting
							onChange={onChangeButtonLabel}
							// eslint-disable-next-line camelcase
							value={button_label}
						/>
					</DonateButton>
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
