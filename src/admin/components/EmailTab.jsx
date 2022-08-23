import { Fragment, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Button,
	RadioControl,
	TextControl,
	ToggleControl,
} from '../../common/components/controls';
import React from 'react';
import Divider from '../../common/components/Divider';
import { useFormContext } from 'react-hook-form';
import apiFetch from '@wordpress/api-fetch';
import { useNotificationContext } from '../contexts/NotificationContext';
import { MailIcon } from '@heroicons/react/outline';

const EmailTab = () => {
	const { watch, getValues } = useFormContext();
	const { createNotification } = useNotificationContext();
	const [isEmailBusy, setIsEmailBusy] = useState(false);
	const watchCustom = watch('_kudos_smtp_enable');
	const watchSendReceipts = watch('_kudos_email_receipt_enable');

	const sendTestEmail = () => {
		const address = getValues('test_email_address');
		setIsEmailBusy(true);
		apiFetch({
			path: 'kudos/v1/email/test',
			headers: {
				Accept: 'application/json',
				'Content-Type': 'application/json',
			},
			method: 'POST',
			body: JSON.stringify({ email: address }),
		})
			.then((result) => {
				createNotification(result.data, result.success);
			})
			.catch((error) => {
				createNotification(error.message);
			})
			.finally(() => {
				setIsEmailBusy(false);
			});
	};

	return (
		<Fragment>
			<h2>{__('Email receipts', 'kudos-donations')}</h2>
			<ToggleControl
				label={__('Send email receipts', 'kudos-donations')}
				help={__(
					'Once a payment has been completed, you can automatically send an email receipt to the donor.',
					'kudos-donations'
				)}
				name="_kudos_email_receipt_enable"
			/>
			{watchSendReceipts && (
				<>
					<TextControl
						label={__('Send receipt copy to', 'kudos-donations')}
						name="_kudos_email_bcc"
						help={__(
							'Add an email address here to send a copy of every donation receipt.',
							'kudos-donations'
						)}
					/>
					<ToggleControl
						name="_kudos_smtp_enable"
						label={__(
							'Use custom SMTP settings',
							'kudos-donations'
						)}
						help={__(
							'Allow you to configure an external SMTP email server.',
							'kudos-donations'
						)}
					/>
					{watchCustom && (
						<Fragment>
							<Divider />
							<h2>{__('SMTP settings', 'kudos-donations')}</h2>
							<TextControl
								name="_kudos_custom_smtp.host"
								label={__('Host', 'kudos-donations')}
								help={__(
									'The email server.',
									'kudos-donations'
								)}
								validation={{
									required: __(
										'The field is required.',
										'kudos-donations'
									),
								}}
							/>
							<TextControl
								name="_kudos_custom_smtp.port"
								label={__('Port', 'kudos-donations')}
								type="number"
								help={__(
									'The email server port number.',
									'kudos-donations'
								)}
								validation={{
									required: __(
										'The field is required.',
										'kudos-donations'
									),
								}}
							/>
							<RadioControl
								name="_kudos_custom_smtp.encryption"
								label={__('Encryption', 'kudos-donations')}
								help={__(
									'For most servers TLS is the recommended option. If your SMTP provider offers both SSL and TLS options, we recommend using TLS.',
									'kudos-donations'
								)}
								options={[
									{
										label: __('None', 'kudos-donations'),
										value: 'none',
										id: 'none',
									},
									{
										label: __('SSL', 'kudos-donations'),
										value: 'ssl',
										id: 'ssl',
									},
									{
										label: __('TLS', 'kudos-donations'),
										value: 'tls',
										id: 'tls',
									},
								]}
							/>
							{/*<CheckboxControl*/}
							{/*	name="_kudos_custom_smtp.autotls"*/}
							{/*	label={__('Auto TLS', 'kudos-donations')}*/}
							{/*/>*/}
							<TextControl
								name="_kudos_custom_smtp.username"
								label={__('Username', 'kudos-donations')}
								help={__(
									'This is usually an email address.',
									'kudos-donations'
								)}
								placeholder="user@domain.com"
								validation={{
									required: __(
										'The field is required.',
										'kudos-donations'
									),
								}}
							/>
							<TextControl
								name="_kudos_custom_smtp.password"
								label={__('Password', 'kudos-donations')}
								help={__(
									'This password will be stored as plain text in the database.',
									'kudos-donations'
								)}
								type="password"
								placeholder="*****"
								validation={{
									required: __(
										'The field is required.',
										'kudos-donations'
									),
								}}
							/>
							<TextControl
								name="_kudos_custom_smtp.from_email"
								label={__('From address', 'kudos-donations')}
								placeholder="user@domain.com"
								help={__(
									'This email address will be used in the "From" field.',
									'kudos-donations'
								)}
								validation={{
									required: __(
										'The field is required.',
										'kudos-donations'
									),
								}}
							/>
							<TextControl
								label={__('Email from name', 'kudos-donations')}
								name="_kudos_custom_smtp.from_name"
								validation={{
									required: __(
										'The field is required.',
										'kudos-donations'
									),
								}}
								help={__(
									'This name will be used in the "From" field.',
									'kudos-donations'
								)}
							/>
						</Fragment>
					)}

					<Divider />
					<h2>{__('Test email', 'kudos-donations')}</h2>
					<div className="space-y-3">
						<TextControl
							label={__('Email address', 'kudos-donations')}
							type="email"
							name="test_email_address"
							help={__(
								'Address to send the test email to.',
								'kudos-donations'
							)}
						/>
						<Button
							isOutline
							type="button"
							isBusy={isEmailBusy}
							onClick={sendTestEmail}
							icon={<MailIcon className="mr-2 w-5 h-5" />}
						>
							Send
						</Button>
					</div>
				</>
			)}
		</Fragment>
	);
};

export { EmailTab };
