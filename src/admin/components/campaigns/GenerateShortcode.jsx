import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalDivider as Divider,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControl as InputControl,
	Button,
	Icon,
	Modal,
	RadioControl,
} from '@wordpress/components';
import { useCopyToClipboard } from '@wordpress/compose';
import { RadioGroupControlBase } from '../controls';

export default function GenerateShortcode({ campaign, iconOnly = false }) {
	const { createSuccessNotice } = useDispatch(noticesStore);
	const [isOpen, setOpen] = useState(false);
	const [type, setType] = useState('button');
	const [label, setLabel] = useState(__('Donate Now', 'kudos-donations'));
	const [alignment, setAlignment] = useState('left');

	const openModal = () => setOpen(true);
	const closeModal = () => setOpen(false);

	const onCopy = () => {
		void createSuccessNotice(__('Shortcode copied', 'kudos-donations'), {
			type: 'snackbar',
			icon: <Icon icon="clipboard" />,
		});
	};

	const copyRef = useCopyToClipboard(
		`[kudos campaign_id="${campaign.id}" type="${type}" ${label && type === 'button' ? 'button_label="' + label + '"' : ''} ${alignment && type === 'button' ? 'alignment="' + alignment + '"' : ''}]`,
		onCopy
	);

	return (
		<>
			{iconOnly ? (
				<Button
					size="compact"
					icon="shortcode"
					label="Get shortcode"
					onClick={openModal}
				/>
			) : (
				<Button
					icon="shortcode"
					variant="secondary"
					onClick={openModal}
				>
					Shortcode
				</Button>
			)}
			{isOpen && (
				<Modal
					title={__('Generate shortcode', 'kudos-donations')}
					onRequestClose={closeModal}
				>
					<RadioGroupControlBase
						label={__('Display as', 'kudos-donations')}
						help={__(
							'Choose the available payment frequency.',
							'kudos-donations'
						)}
						onChange={(value) => setType(value)}
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						isBlock
						value={type}
						options={[
							{
								label: __(
									'Button with modal',
									'kudos-donations'
								),
								value: 'button',
							},
							{
								label: __('Embedded form', 'kudos-donations'),
								value: 'form',
							},
						]}
					/>
					<Divider margin="5" />
					<InputControl
						name="label"
						disabled={type === 'form'}
						label={__('Button label', 'kudos-donations')}
						help={__('Add a button label', 'kudos-donations')}
						value={label}
						onChange={setLabel}
					/>
					<Divider margin="5" />
					<RadioControl
						label="Alignment"
						onChange={setAlignment}
						disabled={type === 'form'}
						selected={alignment}
						options={[
							{
								label: 'Left',
								value: 'left',
							},
							{
								label: 'Center',
								value: 'center',
							},
							{
								label: 'Right',
								value: 'right',
							},
						]}
					/>
					<Divider margin="5" />
					<Button
						ref={copyRef}
						type="button"
						icon="clipboard"
						variant="secondary"
						onClick={closeModal}
					>
						{__('Copy shortcode', 'kudos-donations')}
					</Button>
				</Modal>
			)}
		</>
	);
}
