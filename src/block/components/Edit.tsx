/* eslint-disable camelcase */
import React from 'react';
import type { BlockEditProps } from '@wordpress/blocks';
import { useCampaignContext } from '../contexts/';
import { __ } from '@wordpress/i18n';
import {
	ExternalLink,
	Flex,
	PanelBody,
	RadioControl,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { KudosForm } from './KudosForm';
import { KudosLogo } from './KudosLogo';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import CampaignProvider from '../contexts/campaign-context';
import type { Campaign } from '../../types/entity';
import apiFetch from '@wordpress/api-fetch';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import { EntityRestResponse } from '../../admin/contexts';

export interface KudosButtonAttributes {
	button_label: string;
	type: 'form' | 'button';
	alignment: 'left' | 'center' | 'right';
	campaign_id?: string;
}
const Edit = (props: BlockEditProps<KudosButtonAttributes>) => {
	const { campaign_id } = props.attributes;

	return (
		<CampaignProvider campaignId={campaign_id}>
			<ButtonEdit {...props} />
		</CampaignProvider>
	);
};

interface ButtonEditProps {
	attributes: KudosButtonAttributes;
	setAttributes: (attrs: Partial<ButtonEditProps['attributes']>) => void;
}

type SelectOption = {
	label: string;
	value: string;
	disabled?: boolean;
};

const ButtonEdit = ({
	attributes: { button_label, type, alignment },
	setAttributes,
}: ButtonEditProps) => {
	const { campaign, isLoading: campaignLoaded } = useCampaignContext();
	const blockProps = useBlockProps();
	const [campaigns, setCampaigns] = useState<Campaign[]>([]);

	const fetchPosts = useCallback(async () => {
		const response: EntityRestResponse<Campaign> = await apiFetch({
			path: addQueryArgs('/kudos/v1/campaign', {
				columns: ['id', 'title'],
			}),
		});
		setCampaigns(response.items);
	}, []);

	useEffect(() => {
		void fetchPosts();
	}, [fetchPosts]);

	const onChangeAlignment = (newAlignment: string) => {
		setAttributes({
			alignment:
				newAlignment as ButtonEditProps['attributes']['alignment'],
		});
	};

	const onChangeButtonLabel = (newValue: string) => {
		setAttributes({ button_label: newValue });
	};

	const onChangeCampaign = (newValue: string) => {
		setAttributes({ campaign_id: newValue });
	};

	const onChangeType = (newValue: string) => {
		setAttributes({
			type: newValue as ButtonEditProps['attributes']['type'],
		});
	};

	const options: SelectOption[] = [
		...(campaigns?.map((item: Campaign) => ({
			label: item.title,
			value: item.id.toString(),
		})) ?? []),
		{
			label: __('None', 'kudos-donations'),
			value: '',
			disabled: true,
		},
	];

	const CampaignSelector = () => (
		<SelectControl
			label={__('Select Campaign', 'kudos-donations')}
			value={campaign?.id.toString() ?? ''}
			onChange={onChangeCampaign}
			options={options}
			__nextHasNoMarginBottom
			__next40pxDefaultSize
		/>
	);

	return (
		<div {...blockProps}>
			{campaigns && (
				<InspectorControls>
					<PanelBody
						title={__('Campaign Settings', 'kudos-donations')}
						initialOpen={true}
					>
						<CampaignSelector />
						{campaign?.id ? (
							<ExternalLink
								href={`admin.php?page=kudos-campaigns&post=${campaign.id}`}
							>
								{__('Edit', 'kudos-donations') +
									' ' +
									campaign?.title}
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
							<>
								<TextControl
									label={__(
										'Button Label',
										'kudos-donations'
									)}
									value={button_label}
									onChange={onChangeButtonLabel}
									__nextHasNoMarginBottom
									__next40pxDefaultSize
								/>
								<RadioControl
									label="Alignment"
									selected={alignment}
									onChange={onChangeAlignment}
									options={[
										{ label: 'Left', value: 'left' },
										{ label: 'Center', value: 'center' },
										{ label: 'Right', value: 'right' },
									]}
								/>
							</>
						)}
					</PanelBody>
				</InspectorControls>
			)}

			{campaign ? (
				<KudosForm
					displayAs={type}
					label={button_label}
					previewMode={true}
					alignment={alignment}
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

export default Edit;
