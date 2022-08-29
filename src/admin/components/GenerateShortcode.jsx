import React from 'react';
import { Fragment, useState } from '@wordpress/element';
import { useCopyToClipboard } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import {
	Button,
	RadioGroupControl,
	TextControl,
} from '../../common/components/controls';
import Divider from '../../common/components/Divider';
import KudosModal from '../../common/components/KudosModal';
import { useFormContext } from 'react-hook-form';
import { useNotificationContext } from '../contexts/NotificationContext';
import { ClipboardIcon } from '@heroicons/react/24/outline';

function GenerateShortcode({ campaign }) {
	const [isModalOpen, setIsModalOpen] = useState(false);
	const { createNotification } = useNotificationContext();
	const toggleModal = () => setIsModalOpen((prev) => !prev);

	const methods = useFormContext();

	const { watch } = methods;

	const watchShowAs = watch('shortcode.showAs');
	const watchButtonLabel = watch('shortcode.buttonLabel');

	const onCopy = () => {
		setIsModalOpen(false);
		createNotification(__('Shortcode copied', 'kudos-donations'), true);
	};

	const copyRef = useCopyToClipboard(
		`[kudos campaign_id="${campaign.id}" type="${watchShowAs}" ${
			watchButtonLabel && watchShowAs === 'button'
				? 'button_label="' + watchButtonLabel + '"'
				: ''
		}]`,
		onCopy
	);

	return (
		<Fragment>
			<Button isOutline onClick={toggleModal} type="button">
				{__('Generate shortcode', 'kudos-donations')}
			</Button>

			<KudosModal
				showLogo={false}
				isOpen={isModalOpen}
				toggleModal={toggleModal}
			>
				<>
					<h1 className="text-center">
						{__('Generate shortcode', 'kudos-donations')}
					</h1>
					<RadioGroupControl
						name="shortcode.showAs"
						label={__('Display as', 'kudos-donations')}
						help={__(
							'Choose whether to show Kudos as a button or an embedded form.',
							'kudos-donations'
						)}
						options={[
							{
								label: __(
									'Button with pop-up',
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
					{watchShowAs === 'button' && (
						<>
							<Divider />
							<TextControl
								name="shortcode.buttonLabel"
								help={__(
									'Add a button label',
									'kudos-donations'
								)}
								label={__('Button label', 'kudos-donations')}
							/>
						</>
					)}
					<div className="mt-8 flex justify-end relative">
						<Button ref={copyRef} type="button">
							<ClipboardIcon className="mr-2 w-5 h-5" />
							{__('Copy shortcode', 'kudos-donations')}
						</Button>
					</div>
				</>
			</KudosModal>
		</Fragment>
	);
}

export default GenerateShortcode;
