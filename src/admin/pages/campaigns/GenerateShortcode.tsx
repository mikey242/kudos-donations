import { useState } from '@wordpress/element';
import type { ReactNode } from 'react';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { copy as copyIcon, shortcode as shortcodeIcon } from '@wordpress/icons';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControl as InputControl,
	Button,
	Modal,
	RadioControl,
} from '@wordpress/components';
import { useCopyToClipboard } from '@wordpress/compose';
import { RadioGroupControlBase } from '../../controls';
import type { Campaign } from '../../../types/entity';

interface GenerateShortcodeProps {
	campaign: Campaign;
	iconOnly?: boolean;
}

export default function GenerateShortcode({
	campaign,
	iconOnly = false,
}: GenerateShortcodeProps): ReactNode {
	const { createSuccessNotice } = useDispatch(noticesStore);
	const [isOpen, setOpen] = useState<boolean>(false);
	const [type, setType] = useState<string>('button');
	const [label, setLabel] = useState<string>(
		__('Donate Now', 'kudos-donations')
	);
	const [alignment, setAlignment] = useState<string>('left');

	const openModal = () => setOpen(true);
	const closeModal = () => setOpen(false);

	const onCopy = () => {
		closeModal();
		void createSuccessNotice(__('Shortcode copied', 'kudos-donations'), {
			type: 'snackbar',
		});
	};

	const shortcode = [
		`[kudos campaign_id="${campaign.id}"`,
		`type="${type}"`,
		type === 'button' ? `button_label="${label}"` : '',
		type === 'button' ? `alignment="${alignment}"` : '',
		`]`,
	];

	const copyRef = useCopyToClipboard(
		shortcode.filter(Boolean).join(' '),
		onCopy
	);

	return (
		<>
			{iconOnly ? (
				<Button
					size="compact"
					icon={shortcodeIcon}
					label="Get shortcode"
					onClick={openModal}
				/>
			) : (
				<Button
					icon={shortcodeIcon}
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
						onChange={(value: string) => setType(value)}
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
					<hr className="kudos-separator" />
					<InputControl
						name="label"
						disabled={type === 'form'}
						label={__('Button label', 'kudos-donations')}
						help={__('Add a button label', 'kudos-donations')}
						value={label}
						onChange={setLabel}
						__next40pxDefaultSize
					/>
					<hr className="kudos-separator" />
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
					<hr className="kudos-separator" />
					<Button
						ref={copyRef}
						type="button"
						icon={copyIcon}
						variant="secondary"
					>
						{__('Copy shortcode', 'kudos-donations')}
					</Button>
				</Modal>
			)}
		</>
	);
}
