import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Button,
	CheckboxControl,
	RadioControl,
	TextControl,
	ToggleControl,
} from '../../../../common/components/controls';
import React from 'react';
import Divider from '../../Divider';
import { useFormContext } from 'react-hook-form';
import apiFetch from '@wordpress/api-fetch';

const EmailTab = ({ createNotification }) => {
	const { watch, getValues } = useFormContext();
	const watchCustom = watch('_kudos_smtp_enable');

	const sendTestEmail = () => {
		const address = getValues('test_email_address');
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
			<TextControl
				label={__('Send receipt copy to:', 'kudos-donations')}
				name="_kudos_email_bcc"
			/>
			<ToggleControl
				name="_kudos_smtp_enable"
				label={__('Use custom SMTP settings', 'kudos-donations')}
			/>
			{watchCustom && (
				<Fragment>
					<Divider />
					<h2>{__('SMTP settings', 'kudos-donations')}</h2>
					<TextControl
						name="_kudos_smtp_host"
						label={__('Host', 'kudos-donations')}
					/>
					<TextControl
						name="_kudos_smtp_port"
						label={__('Port', 'kudos-donations')}
						type="number"
					/>
					<br />
					<RadioControl
						name="_kudos_smtp_encryption"
						label={__('Encryption', 'kudos-donations')}
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
					<CheckboxControl
						name="_kudos_smtp_autotls"
						label={__('Auto TLS', 'kudos-donations')}
					/>
					<br />
					<TextControl
						name="_kudos_smtp_username"
						label={__('Username', 'kudos-donations')}
						help={__(
							'This is usually an email address.',
							'kudos-donations'
						)}
						placeholder="user@domain.com"
					/>
					<TextControl
						name="_kudos_smtp_password"
						label={__('Password', 'kudos-donations')}
						help={__(
							'This password will be stored as plain text in the database.',
							'kudos-donations'
						)}
						type="password"
						placeholder="*****"
					/>
					<br />
					<TextControl
						name="_kudos_smtp_from"
						label={__('From address', 'kudos-donations')}
						placeholder="user@domain.com"
					/>
				</Fragment>
			)}
			<Divider />
			<h2>{__('Test email', 'kudos-donations')}</h2>
			<TextControl
				label={__('Send test email', 'kudos-donations')}
				type="email"
				name="test_email_address"
			/>
			<br />
			<Button isOutline type="button" onClick={sendTestEmail}>
				Send
			</Button>
		</Fragment>
	);
};

export { EmailTab };
