import { __, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useSettingsContext } from '../../../contexts';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Button, Disabled, ExternalLink, Icon } from '@wordpress/components';
import { RadioGroupControl, TextControl } from '../../../controls';
import { Panel, PaymentMethodsList } from '../../../components';
import type { AllSettings } from '../../../../types/all-settings';
import type { AdminPanel } from '../../AdminTabPanel';

type ApiMode = 'live' | 'test';

const ApiModePanel = () => {
	const { settings } = useSettingsContext<AllSettings>();
	const {
		_kudos_vendor_stripe_api_key_live: liveKey,
		_kudos_vendor_stripe_api_key_test: testKey,
	} = settings;

	return (
		<Panel header={__('API Mode', 'kudos-donations')}>
			<RadioGroupControl
				name="_kudos_vendor_stripe_api_mode"
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
	const { _kudos_payment_vendor_status: status } = settings;

	const isReady = status?.ready ?? false;
	const statusText = isReady
		? sprintf(
				/* translators: %s is the payment vendor name */
				__('%s ready', 'kudos-donations'),
				'Stripe'
			)
		: null;

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
			header={__('Payment methods', 'kudos-donations')}
			headerExtra={
				isReady && (
					<strong style={{ color: 'var(--kudos-colour-success)' }}>
						{statusText}
						<Icon icon="yes" />
					</strong>
				)
			}
		>
			<PaymentMethodsList methods={status?.methods} />
			<p>
				{__(
					"These are the payment methods enabled on your Stripe account. Stripe's Payment Element will automatically show the most relevant methods to each donor based on their location and currency.",
					'kudos-donations'
				)}
			</p>
			<Panel.Footer>
				<ExternalLink href="https://dashboard.stripe.com/settings/payment_methods">
					{__(
						'Manage payment methods in Stripe Dashboard',
						'kudos-donations'
					)}
					.
				</ExternalLink>
				<Button
					onClick={refresh}
					type="button"
					variant="link"
					isBusy={checkingApiKey}
					disabled={checkingApiKey}
				>
					{__('Refresh', 'kudos-donations')}
				</Button>
			</Panel.Footer>
		</Panel>
	);
};

const ApiKeysPanel = () => {
	const { checkingApiKey, updateSettings, settings } =
		useSettingsContext<AllSettings>();
	const {
		_kudos_vendor_stripe_api_key_live: liveKey,
		_kudos_vendor_stripe_api_key_test: testKey,
	} = settings;
	const apiKeyStatus: Record<ApiMode, string> = {
		live: liveKey,
		test: testKey,
	};

	return (
		<Panel header={__('API Keys', 'kudos-donations')}>
			{(['live', 'test'] as ApiMode[]).map((mode, i) => {
				const isDisabled =
					!!apiKeyStatus[mode] || window.kudos.admin?.demoMode;
				return (
					<Disabled key={i} isDisabled={isDisabled}>
						<TextControl
							key={i}
							isDisabled={isDisabled}
							name={`_kudos_vendor_stripe_api_key_${mode}`}
							prefix={<Icon icon="shield" />}
							type={isDisabled ? 'password' : 'text'}
							rules={{
								validate: (value: string) =>
									!value ||
									value.startsWith(`rk_${mode}_`) ||
									value.startsWith(`sk_${mode}_`) ||
									sprintf(
										/* translators: 1: restricted key prefix, 2: secret key prefix */
										__(
											'Key must start with "%1$s" or "%2$s"',
											'kudos-donations'
										),
										`rk_${mode}_`,
										`sk_${mode}_`
									),
							}}
							label={mode + ' key'}
						/>
					</Disabled>
				);
			})}
			<Panel.Footer>
				<ExternalLink href="https://dashboard.stripe.com/apikeys">
					{__('Visit Stripe dashboard', 'kudos-donations')}.
				</ExternalLink>
				<Button
					type="button"
					variant="link"
					disabled={checkingApiKey}
					isDestructive={true}
					onClick={() => {
						void updateSettings({
							_kudos_vendor_stripe_api_key_live: '',
							_kudos_vendor_stripe_api_key_test: '',
							_kudos_vendor_stripe_api_mode: 'test',
							_kudos_vendor_stripe_webhook: {},
						});
					}}
				>
					{__('Reset Stripe', 'kudos-donations')}
				</Button>
			</Panel.Footer>
		</Panel>
	);
};

const WebhookPanel = () => {
	const { settings } = useSettingsContext<AllSettings>();
	const {
		_kudos_vendor_stripe_webhook: webhook,
		_kudos_vendor_stripe_api_key_live: liveKey,
		_kudos_vendor_stripe_api_key_test: testKey,
	} = settings;

	// Only show when a key is saved but auto-registration hasn't succeeded yet.
	if (webhook?.secret || (!liveKey && !testKey)) {
		return null;
	}

	const webhookUrl = (window.kudos?.admin?.stripeWebhookUrl as string) ?? '';

	return (
		<Panel header={__('Webhook', 'kudos-donations')}>
			<p>
				{__(
					'Stripe webhook could not be registered automatically. Add the URL below as an endpoint in your Stripe Dashboard, then paste the signing secret.',
					'kudos-donations'
				)}
			</p>
			<code style={{ wordBreak: 'break-all' }}>{webhookUrl}</code>
			<TextControl
				name="_kudos_vendor_stripe_webhook.secret"
				label={__('Signing secret', 'kudos-donations')}
				prefix={<Icon icon="shield" />}
				help={__(
					'Found in the webhook endpoint details in your Stripe Dashboard.',
					'kudos-donations'
				)}
			/>
			<Panel.Footer>
				<ExternalLink href="https://dashboard.stripe.com/webhooks">
					{__('Open Stripe Dashboard', 'kudos-donations')}
				</ExternalLink>
			</Panel.Footer>
		</Panel>
	);
};

export const stripePanels: AdminPanel[] = [
	{ name: 'apimode', content: <ApiModePanel /> },
	{ name: 'payment-methods', content: <PaymentMethodsPanel /> },
	{ name: 'apikeys', content: <ApiKeysPanel /> },
	{ name: 'webhook', content: <WebhookPanel /> },
];
