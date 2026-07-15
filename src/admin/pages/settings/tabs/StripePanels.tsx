import { __, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useSettingsContext } from '../../../contexts';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Button, ExternalLink, Icon } from '@wordpress/components';
import {
	RadioGroupControl,
	SecretControl,
	TextControl,
} from '../../../controls';
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
				void createErrorNotice(response?.message, { type: 'snackbar' });
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

const ApiKeysPanel = () => (
	<Panel header={__('API Keys', 'kudos-donations')}>
		{(['test', 'live'] as ApiMode[]).map((mode) => (
			<SecretControl
				key={mode}
				name={`_kudos_vendor_stripe_api_key_${mode}`}
				label={mode + ' key'}
				validate={(value) =>
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
					)
				}
			/>
		))}
		<Panel.Footer>
			<ExternalLink href="https://dashboard.stripe.com/apikeys">
				{__('Visit Stripe dashboard', 'kudos-donations')}.
			</ExternalLink>
		</Panel.Footer>
	</Panel>
);

const WebhookPanel = () => {
	const { settings } = useSettingsContext<AllSettings>();
	const {
		_kudos_vendor_stripe_webhook: webhook,
		_kudos_vendor_stripe_api_key_live: liveKey,
		_kudos_vendor_stripe_api_key_test: testKey,
	} = settings;
	const mode: ApiMode = settings._kudos_vendor_stripe_api_mode ?? 'test';
	const currentKey = mode === 'live' ? liveKey : testKey;

	// Endpoints are per mode, so only the current mode's secret counts. Show this only when
	// that mode has a key saved but auto-registration hasn't succeeded for it.
	if (webhook?.[mode]?.secret || !currentKey) {
		return null;
	}

	const webhookUrl = (window.kudos?.admin?.stripeWebhookUrl as string) ?? '';

	return (
		<Panel
			header={
				'live' === mode
					? __('Webhook (live mode)', 'kudos-donations')
					: __('Webhook (test mode)', 'kudos-donations')
			}
		>
			<p>
				{'live' === mode
					? __(
							'Stripe webhook could not be registered automatically. Add the URL below as an endpoint in your Stripe Dashboard while it is in live mode, then paste that endpoint’s signing secret below.',
							'kudos-donations'
						)
					: __(
							'Stripe webhook could not be registered automatically. Add the URL below as an endpoint in the sandbox or test mode you are using in your Stripe Dashboard, then paste that endpoint’s signing secret below.',
							'kudos-donations'
						)}
			</p>
			<code style={{ wordBreak: 'break-all' }}>{webhookUrl}</code>
			<TextControl
				name={`_kudos_vendor_stripe_webhook.${mode}.secret`}
				label={__('Signing secret', 'kudos-donations')}
				prefix={<Icon icon="shield" />}
				help={
					'live' === mode
						? __(
								'Found in the endpoint details in your Stripe Dashboard. It must be the live endpoint — a test secret will not verify live payments.',
								'kudos-donations'
							)
						: __(
								'Found in the endpoint details in your Stripe Dashboard. It must be the endpoint for the sandbox or test mode you are using — a live secret will not verify test payments.',
								'kudos-donations'
							)
				}
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
