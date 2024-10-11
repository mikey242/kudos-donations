import { __, sprintf } from '@wordpress/i18n';
import React from 'react';
import { Fragment } from '@wordpress/element';
import { useSettingsContext } from '../../contexts/SettingsContext';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import {
	Button,
	Disabled,
	Flex,
	FlexItem,
	Icon,
	Panel,
	PanelBody,
} from '@wordpress/components';
import { RadioGroupControl, TextControl } from '../controls';

const MollieTab = () => {
	const {
		checkingApiKey,
		checkApiKey,
		updateSettings,
		settings,
		isVendorReady,
	} = useSettingsContext();
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);

	const {
		_kudos_vendor_mollie_payment_methods: paymentMethods,
		_kudos_vendor_mollie_api_key_live: liveKey,
		_kudos_vendor_mollie_api_key_test: testKey,
	} = settings;

	const apiKeyStatus = {
		live: liveKey,
		test: testKey,
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
		<>
			<Panel header={__('API Mode', 'kudos-donations')}>
				<PanelBody>
					<RadioGroupControl
						name="_kudos_vendor_mollie_api_mode"
						label={__('API Mode', 'kudos-donations')}
						options={[
							{
								label: __('Live', 'kudos-donations'),
								value: 'live',
								disabled: !apiKeyStatus.live,
							},
							{
								label: __('Test', 'kudos-donations'),
								value: 'test',
								disabled: !apiKeyStatus.test,
							},
						]}
						help={__(
							'When using Kudos Donations for the first time, the payment mode is set to "Test". Check that the configuration is working correctly. Once you are ready to receive live payments you can switch the mode to "Live".',
							'kudos-donations'
						)}
					/>
				</PanelBody>
			</Panel>
			{paymentMethods.length > 0 && (
				<Panel
					header={__('Available payment methods', 'kudos-donations')}
				>
					<PanelBody>
						<Flex wrap={true} justify="none" direction="row">
							{paymentMethods?.map((method) => (
								<FlexItem key={method?.id}>
									<Flex>
										<img
											alt={method.description + ' icon'}
											src={method.image}
										/>

										<strong>{method?.description}</strong>
									</Flex>
								</FlexItem>
							))}
						</Flex>
						<Flex justify="end">
							<Button
								onClick={refresh}
								type="button"
								variant="link"
								isBusy={checkingApiKey}
								disabled={!isVendorReady}
								// icon="update"
							>
								{__(
									'Refresh Payment Methods',
									'kudos-donations'
								)}
							</Button>
						</Flex>
					</PanelBody>
				</Panel>
			)}
			<Panel header={__('API Keys', 'kudos-donations')}>
				<PanelBody>
					{['live', 'test'].map((mode, i) => {
						const isDisabled = apiKeyStatus[mode];
						return (
							<Disabled key={i} isDisabled={isDisabled}>
								<TextControl
									key={i}
									isDisabled={isDisabled}
									name={`_kudos_vendor_mollie_api_key_${mode}`}
									prefix={<Icon icon="shield" />}
									type={isDisabled ? 'password' : 'text'}
									rules={{
										validate: (value) =>
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
									label={
										<span className="capitalize">
											{mode} key
										</span>
									}
								/>
							</Disabled>
						);
					})}
					<Flex justify="flex-end">
						<Button
							type="button"
							variant="link"
							disabled={checkingApiKey}
							isDestructive={true}
							onClick={() => {
								updateSettings({
									_kudos_vendor_mollie_recurring: false,
									_kudos_vendor_mollie_api_key_live: '',
									_kudos_vendor_mollie_api_key_test: '',
									_kudos_vendor_mollie_api_mode: 'test',
								});
							}}
						>
							{__('Reset Mollie', 'kudos-donations')}
						</Button>
					</Flex>
				</PanelBody>
			</Panel>
		</>
	);
};

export default MollieTab;
