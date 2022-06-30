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
// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';

const ButtonEdit = (props) => {
	const [campaigns, setCampaigns] = useState(null);
	const [currentCampaign, setCurrentCampaign] = useState(null);

	const {
		className,
		// eslint-disable-next-line camelcase
		attributes: { button_label, campaign_id, type, alignment },
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
	}, []);

	const onChangeButtonLabel = (newValue) => {
		setAttributes({ button_label: newValue });
	};

	const onChangeAlignment = (newValue) => {
		setAttributes({
			alignment: newValue === undefined ? 'none' : newValue,
		});
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
					<style>{`:root {--kudos-theme-primary: ${
						currentCampaign?.meta.theme_color ?? '#ff9f1c'
					}`}</style>
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

					<BlockControls>
						<AlignmentToolbar
							value={alignment}
							onChange={onChangeAlignment}
						/>
					</BlockControls>

					<KudosButton
						className={
							(className ?? '') + ' has-text-align-' + alignment
						}
					>
						<RichText
							allowedFormats={[]} // Disable all formatting
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
