import { __, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useSettingsContext } from '../../../contexts';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Button, ExternalLink, Icon } from '@wordpress/components';
import { RadioGroupControl, SecretControl } from '../../../controls';
import { Panel, PaymentMethodsList } from '../../../components';
import type { AllSettings } from '../../../../types/all-settings';
import type { AdminPanel } from '../../AdminTabPanel';

type ApiMode = 'live' | 'test';

const ApiModePanel = () => {
	const { settings } = useSettingsContext<AllSettings>();
	const {
		_kudos_vendor_mollie_api_key_live: liveKey,
		_kudos_vendor_mollie_api_key_test: testKey,
	} = settings;

	return (
		<Panel header={__('API Mode', 'kudos-donations')}>
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
	const { _kudos_payment_vendor_status: status } = settings;

	const isReady = status?.ready ?? false;
	const statusText = isReady
		? sprintf(
				/* translators: %s is the payment vendor name */
				__('%s ready', 'kudos-donations'),
				'Mollie'
			) + (status?.account ? ` (${status.account})` : '')
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
			header={__('Available payment methods', 'kudos-donations')}
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

const ApiKeysPanel = () => (
	<Panel header={__('API Keys', 'kudos-donations')}>
		{(['test', 'live'] as ApiMode[]).map((mode) => (
			<SecretControl
				key={mode}
				name={`_kudos_vendor_mollie_api_key_${mode}`}
				label={mode + ' key'}
				validate={(value) =>
					!value ||
					value.startsWith(mode) ||
					sprintf(
						/* translators: %s is the api mode */
						__('Key must start with "%s"', 'kudos-donations'),
						mode
					)
				}
			/>
		))}
		<Panel.Footer>
			<ExternalLink href="https://my.mollie.com/dashboard/developers/api-keys">
				{__('Visit Mollie dashboard', 'kudos-donations')}.
			</ExternalLink>
		</Panel.Footer>
	</Panel>
);

export const molliePanels: AdminPanel[] = [
	{ name: 'apimode', content: <ApiModePanel /> },
	{ name: 'status', content: <PaymentMethodsPanel /> },
	{ name: 'apikeys', content: <ApiKeysPanel /> },
];
