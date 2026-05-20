import { __, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useSettingsContext } from '../../../contexts';
import {
	Button,
	Disabled,
	ExternalLink,
	Flex,
	Icon,
} from '@wordpress/components';
import { RadioGroupControl, TextControl } from '../../../controls';
import { Panel } from '../../../components';
import type { AllSettings } from '../../../../types/all-settings';

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
							_kudos_payment_vendor_status: {},
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
			<Flex direction="column" gap={2}>
				<code style={{ wordBreak: 'break-all' }}>{webhookUrl}</code>
				<ExternalLink href="https://dashboard.stripe.com/webhooks">
					{__('Open Stripe Dashboard', 'kudos-donations')}
				</ExternalLink>
			</Flex>
			<TextControl
				name="_kudos_vendor_stripe_webhook.secret"
				label={__('Signing secret', 'kudos-donations')}
				prefix={<Icon icon="shield" />}
				help={__(
					'Found in the webhook endpoint details in your Stripe Dashboard.',
					'kudos-donations'
				)}
			/>
		</Panel>
	);
};

export const StripePanels = (): React.ReactNode => (
	<>
		<ApiModePanel />
		<ApiKeysPanel />
		<WebhookPanel />
	</>
);
