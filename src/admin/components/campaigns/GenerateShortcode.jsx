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
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalToggleGroupControl as ToggleGroupControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	Icon,
	Modal,
} from '@wordpress/components';
import { useCopyToClipboard } from '@wordpress/compose';

export default function GenerateShortcode({ campaign, iconOnly = false }) {
	const { createSuccessNotice } = useDispatch(noticesStore);
	const [isOpen, setOpen] = useState(false);
	const [type, setType] = useState('button');
	const [label, setLabel] = useState(__('Donate Now', 'kudos-donations'));

	const openModal = () => setOpen(true);
	const closeModal = () => setOpen(false);

	const onCopy = () => {
		void createSuccessNotice(__('Shortcode copied', 'kudos-donations'), {
			type: 'snackbar',
			icon: <Icon icon="clipboard" />,
		});
	};

	const copyRef = useCopyToClipboard(
		`[kudos campaign_id="${campaign.id}" type="${type}" ${
			label && type === 'button' ? 'button_label="' + label + '"' : ''
		}]`,
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
					<ToggleGroupControl
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
					>
						<ToggleGroupControlOption
							value="button"
							label={__('Button with modal', 'kudos-donations')}
						/>
						<ToggleGroupControlOption
							value="form"
							label={__('Embedded form', 'kudos-donations')}
						/>
					</ToggleGroupControl>
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
