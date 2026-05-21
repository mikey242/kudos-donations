import { __, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useSettingsContext } from '../../../contexts';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Button, Disabled, ExternalLink, Icon } from '@wordpress/components';
import { RadioGroupControl, TextControl } from '../../../controls';
import { Panel, PaymentMethodsList } from '../../../components';
import type { AllSettings } from '../../../../types/all-settings';

type ApiMode = 'live' | 'test';

const ApiModePanel = () => {
	const { settings } = useSettingsContext<AllSettings>();
	const {
		_kudos_vendor_mollie_api_key_live: liveKey,
		_kudos_vendor_mollie_api_key_test: testKey,
	} = settings;

	return (
		<Panel name="apimode" header={__('API Mode', 'kudos-donations')}>
			<RadioGroupControl
				name="_kudos_vendor_mollie_api_mode"
				label={__('API Mode', 'kudos-donations')}
				options={[
					{
						label: __('Live', 'kudos-donations'),
						value: 'live',
						disabled: !liveKey,
					},
					{
						label: __('Test', 'kudos-donations'),
						value: 'test',
						disabled: !testKey,
					},
				]}
				help={__(
					'When using Kudos Donations for the first time, the payment mode is set to "Test". Check that the configuration is working correctly. Once you are ready to receive live payments you can switch the mode to "Live".',
					'kudos-donations'
				)}
			/>
		</Panel>
	);
};

const PaymentMethodsPanel = () => {
	const { checkingApiKey, checkApiKey, settings } =
		useSettingsContext<AllSettings>();
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);
	const { _kudos_vendor_mollie_payment_methods: paymentMethods } = settings;

	const renderPaymentMethods = (): React.ReactNode => {
		if (paymentMethods.length === 0) {
			return <i>{__('No payment methods found.', 'kudos-donations')}</i>;
		}

		return (
			<Flex wrap justify="none" direction="row">
				{paymentMethods.map((method) => (
					<FlexItem key={method.id}>
						<Flex>
							<img
								alt={`${method.description} icon`}
								src={method.image}
							/>
							<strong>{method.description}</strong>
						</Flex>
					</FlexItem>
				))}
			</Flex>
		);
	};

	const refresh = () => {
		checkApiKey().then((response) => {
			if (response.success) {
				void createSuccessNotice(response?.message, {
					type: 'snackbar',
				});
			} else {
				void createErrorNotice(response?.message);
			}
		});
	};

	return (
		<Panel
			name="status"
			header={__('Available payment methods', 'kudos-donations')}
			headerExtra={
				<strong style={{ color: 'var(--kudos-colour-success)' }}>
					{settings._kudos_payment_vendor_status.text}
					{settings._kudos_payment_vendor_status.ready && (
						<Icon icon="yes" />
					)}
				</strong>
			}
		>
			<PaymentMethodsList methods={status?.methods} />
			<p>
				{__(
					"These are the payment methods available to your donors. In order to use recurring payments (subscriptions) you will need to have either 'Card', 'PayPal' or 'SEPA Direct Debit'. Please note that 'SEPA Direct Debit' does not appear as a payment option as it uses other payment options to set-up the subscription. If you have made changes to the payment methods in your Mollie dashboard, please click the refresh button below to update this list.",
					'kudos-donations'
				)}
			</p>
			<Panel.Footer>
				<ExternalLink href="https://help.mollie.com/hc/articles/115000470109-What-is-Mollie-Recurring">
					{__(
						'Read more about Mollie recurring payments',
						'kudos-donations'
					)}
				</ExternalLink>
				<Button
					onClick={refresh}
					type="button"
					variant="link"
					isBusy={checkingApiKey}
					disabled={checkingApiKey}
				>
					{__('Refresh Payment Methods', 'kudos-donations')}
				</Button>
			</Panel.Footer>
		</Panel>
	);
};

const ApiKeysPanel = () => {
	const { checkingApiKey, updateSettings, settings } =
		useSettingsContext<AllSettings>();
	const {
		_kudos_vendor_mollie_api_key_live: liveKey,
		_kudos_vendor_mollie_api_key_test: testKey,
	} = settings;
	const apiKeyStatus: Record<ApiMode, string> = {
		live: liveKey,
		test: testKey,
	};

	return (
		<Panel name="apikeys" header={__('API Keys', 'kudos-donations')}>
			{(['live', 'test'] as ApiMode[]).map((mode, i) => {
				const isDisabled =
					!!apiKeyStatus[mode] || window.kudos.admin?.demoMode;
				return (
					<Disabled key={i} isDisabled={isDisabled}>
						<TextControl
							key={i}
							isDisabled={isDisabled}
							name={`_kudos_vendor_mollie_api_key_${mode}`}
							prefix={<Icon icon="shield" />}
							type={isDisabled ? 'password' : 'text'}
							rules={{
								validate: (value: string) =>
									!value ||
									value.startsWith(mode) ||
									sprintf(
										/* translators: %s is the api mode */
										__(
											'Key must start with "%s"',
											'kudos-donations'
										),
										mode
									),
							}}
							label={mode + ' key'}
						/>
					</Disabled>
				);
			})}
			<Panel.Footer>
				<ExternalLink href="https://my.mollie.com/dashboard/developers/api-keys">
					{__('Visit Mollie dashboard', 'kudos-donations')}.
				</ExternalLink>
				<Button
					type="button"
					variant="link"
					disabled={checkingApiKey}
					isDestructive={true}
					onClick={() => {
						void updateSettings({
							_kudos_vendor_mollie_recurring: false,
							_kudos_vendor_mollie_api_key_live: '',
							_kudos_vendor_mollie_api_key_test: '',
							_kudos_vendor_mollie_api_mode: 'test',
							_kudos_vendor_mollie_profile: null,
							_kudos_payment_vendor_status: {},
						});
					}}
				>
					{__('Reset Mollie', 'kudos-donations')}
				</Button>
			</Panel.Footer>
		</Panel>
	);
};

export const MolliePanels = (): React.ReactNode => (
	<>
		<ApiModePanel />
		<PaymentMethodsPanel />
		<ApiKeysPanel />
	</>
);
