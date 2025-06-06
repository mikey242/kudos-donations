import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { useFormContext } from 'react-hook-form';
// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { useSettingsContext } from '../../../contexts';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Button, Disabled, Flex, Icon, PanelRow } from '@wordpress/components';
import { RadioControl, TextControl, ToggleControl } from '../../controls';
import type { WPErrorResponse, WPResponse } from '../../../../types/wp';
import { Panel } from '../../Panel';

const EmailTab = (): React.ReactNode => {
	const { watch, getValues } = useFormContext();
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);
	const { updateSetting, settings } = useSettingsContext();
	const passwordDisabled = !!settings._kudos_smtp_password;
	const [isEmailBusy, setIsEmailBusy] = useState(false);
	const watchCustom = watch('_kudos_smtp_enable');
	const watchSendReceipts = watch('_kudos_email_receipt_enable');

	const sendTestEmail = (): void => {
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
			.then((result: WPResponse) => {
				void createSuccessNotice(result.message, {
					type: 'snackbar',
				});
			})
			.catch((error: WPErrorResponse) => {
				void createErrorNotice(error.message, { type: 'snackbar' });
			})
			.finally(() => {
				setIsEmailBusy(false);
			});
	};

	return (
		<>
			<Panel header={__('Email receipts', 'kudos-donations')}>
				<ToggleControl
					label={__('Send email receipts', 'kudos-donations')}
					help={__(
						'Once a payment has been completed, you can automatically send an email receipt to the donor.',
						'kudos-donations'
					)}
					name="_kudos_email_receipt_enable"
				/>
				<ToggleControl
					label={__('Show campaign name', 'kudos-donations')}
					isDisabled={!watchSendReceipts}
					help={__(
						'Show the campaign name in the receipt email.',
						'kudos-donations'
					)}
					name="_kudos_email_show_campaign_name"
				/>
				<TextControl
					label={__('Send receipt copy to', 'kudos-donations')}
					isDisabled={!watchSendReceipts}
					name="_kudos_email_bcc"
					help={__(
						'Add an email address here to send a copy of every donation receipt.',
						'kudos-donations'
					)}
				/>
			</Panel>
			<Panel header={__('SMTP settings', 'kudos-donations')}>
				<PanelRow>
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
				</PanelRow>
				{watchCustom && (
					<>
						<TextControl
							name="_kudos_custom_smtp.host"
							label={__('Host', 'kudos-donations')}
							help={__('The email server.', 'kudos-donations')}
							rules={{
								required: __(
									'This field is required.',
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
							rules={{
								required: __(
									'This field is required.',
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
								},
								{
									label: __('SSL', 'kudos-donations'),
									value: 'ssl',
								},
								{
									label: __('TLS', 'kudos-donations'),
									value: 'tls',
								},
							]}
						/>
						<TextControl
							name="_kudos_custom_smtp.username"
							label={__('Username', 'kudos-donations')}
							help={__(
								'This is usually an email address.',
								'kudos-donations'
							)}
							placeholder="user@domain.com"
							rules={{
								required: __(
									'This field is required.',
									'kudos-donations'
								),
							}}
						/>
						<Disabled isDisabled={passwordDisabled}>
							<TextControl
								name="_kudos_smtp_password"
								prefix={<Icon icon="shield" />}
								label={__('Password', 'kudos-donations')}
								help={__(
									'This password will be encrypted in the database.',
									'kudos-donations'
								)}
								type="password"
								rules={{
									required: __(
										'This field is required.',
										'kudos-donations'
									),
								}}
							/>
						</Disabled>
						<Flex justify="flex-end">
							<Button
								type="button"
								variant="link"
								isDestructive={true}
								className="ml-auto text-red-600 underline text-right cursor-pointer block"
								onClick={() =>
									updateSetting('_kudos_smtp_password', '')
								}
							>
								{__('Reset password', 'kudos-donations')}
							</Button>
						</Flex>

						<TextControl
							name="_kudos_custom_smtp.from_email"
							label={__('From address', 'kudos-donations')}
							placeholder="user@domain.com"
							help={__(
								'This email address will be used in the "From" field.',
								'kudos-donations'
							)}
							rules={{
								required: __(
									'This field is required.',
									'kudos-donations'
								),
							}}
						/>

						<TextControl
							label={__('Email from name', 'kudos-donations')}
							name="_kudos_custom_smtp.from_name"
							rules={{
								required: __(
									'This field is required.',
									'kudos-donations'
								),
							}}
							help={__(
								'This name will be used in the "From" field.',
								'kudos-donations'
							)}
						/>
					</>
				)}
			</Panel>
			<Panel header={__('Test email', 'kudos-donations')}>
				<TextControl
					label={__('Email address', 'kudos-donations')}
					prefix="@"
					type="email"
					name="test_email_address"
					help={__(
						'Address to send the test email to. Please ensure you save changes first.',
						'kudos-donations'
					)}
				/>
				<Flex justify="flex-end">
					<Button
						type="button"
						variant="secondary"
						isBusy={isEmailBusy}
						onClick={sendTestEmail}
						icon="email"
					>
						{__('Send', 'kudos-donations')}
					</Button>
				</Flex>
			</Panel>
		</>
	);
};

export { EmailTab };
