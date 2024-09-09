import { __, sprintf } from '@wordpress/i18n';
import React from 'react';
import { Fragment } from '@wordpress/element';
import { useSettingsContext } from '../../contexts/SettingsContext';
import { ArrowTopRightOnSquareIcon } from '@heroicons/react/16/solid';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import {
	Button,
	Disabled,
	Flex,
	Icon,
	Panel,
	PanelBody,
} from '@wordpress/components';
import { RadioGroupControl, TextControl } from '../controls';

const MollieTab = () => {
	const { checkingApiKey, checkApiKey, updateSettings, settings } =
		useSettingsContext();
	const { createSuccessNotice } = useDispatch(noticesStore);
	const apiKeyStatus = {
		live: settings._kudos_vendor_mollie_api_key_live,
		test: settings._kudos_vendor_mollie_api_key_test,
	};

	const refresh = () => {
		checkApiKey().then((response) => {
			void createSuccessNotice(response?.message);
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
					<Button
						onClick={refresh}
						type="button"
						variant="secondary"
						isBusy={checkingApiKey}
						icon="update"
					>
						{__('Refresh API', 'kudos-donations')}
					</Button>

					<p className="my-1 text-sm text-gray-500">
						{__(
							'Use this if you have made changes in Mollie such as enabling SEPA Direct Debit or credit card.',
							'kudos-donations'
						)}
					</p>
				</PanelBody>
			</Panel>
			<Panel
				header={__('API Keys', 'kudos-donations')}
				help={
					<>
						You can get your Mollie API keys{' '}
						<a
							target="_blank"
							rel="noreferrer"
							className="underline inline-flex items-center"
							href="https://my.mollie.com/dashboard/developers/api-keys"
						>
							here{' '}
							<ArrowTopRightOnSquareIcon className="w-5 h-5 ml-1" />
						</a>
						.
					</>
				}
			>
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
									validation={{
										validate: (value) =>
											value.startsWith(mode) ||
											sprintf(
												// translators: %s is the api mode
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
