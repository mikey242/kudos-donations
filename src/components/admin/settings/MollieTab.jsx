import { __, sprintf } from '@wordpress/i18n';
import React from 'react';
import { RadioGroupControl, TextControl } from '../../common/controls';
import { Fragment } from '@wordpress/element';
import Divider from '../../common/Divider';
import { clsx } from 'clsx';
import { useSettingsContext } from '../../common/contexts/SettingsContext';
import { ArrowPathIcon } from '@heroicons/react/24/outline';
import { Panel } from '../../common/Panel';
import { ArrowTopRightOnSquareIcon } from '@heroicons/react/16/solid';
import { useNotificationContext } from '../../common/contexts/NotificationContext';

const MollieTab = ({ checkApiKeys }) => {
	const { checkingApiKey, updateSettings, settings } = useSettingsContext();
	const { createNotification } = useNotificationContext();
	const apiKeyStatus = {
		live: settings._kudos_vendor_mollie_api_key_live,
		test: settings._kudos_vendor_mollie_api_key_test,
	};

	const refresh = () => {
		checkApiKeys().then((response) => {
			createNotification(response.data, response?.success);
		});
	};

	return (
		<Fragment>
			<Panel title={__('API Mode', 'kudos-donations')}>
				<RadioGroupControl
					name="_kudos_vendor_mollie_api_mode"
					label={__('API Mode', 'kudos-donations')}
					hideLabel={true}
					options={[
						{
							label: __('Test', 'kudos-donations'),
							value: 'test',
							disabled: !apiKeyStatus.test,
						},
						{
							label: __('Live', 'kudos-donations'),
							value: 'live',
							disabled: !apiKeyStatus.live,
						},
					]}
					help={__(
						'When using Kudos Donations for the first time, the payment mode is set to "Test". Check that the configuration is working correctly. Once you are ready to receive live payments you can switch the mode to "Live".',
						'kudos-donations'
					)}
				/>
				<Divider />
				<div>
					<button
						className="inline-flex items-center cursor-pointer"
						onClick={refresh}
						type="button"
					>
						<>
							<ArrowPathIcon
								className={clsx(
									checkingApiKey && 'animate-spin',
									'w-5 h-5'
								)}
							/>{' '}
							<span className="mx-2">
								{__('Refresh API', 'kudos-donations')}
							</span>
						</>
					</button>
					<p className="my-1 text-sm text-gray-500">
						{__(
							'Use this if you have made changes in Mollie such as enabling SEPA Direct Debit or credit card.',
							'kudos-donations'
						)}
					</p>
				</div>
			</Panel>
			<Panel
				title={__('API Keys', 'kudos-donations')}
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
				{['live', 'test'].map((mode, i) => {
					const isDisabled = apiKeyStatus[mode];
					return (
						<TextControl
							key={i}
							name={`_kudos_vendor_mollie_api_key_${mode}`}
							isDisabled={isDisabled}
							validation={{
								pattern: {
									value: new RegExp(`^$|^${mode}`), // Dynamically create the pattern
									message: sprintf(
										// translators: %s is the api mode.
										__(
											"API Key must start with '%s'",
											'kudos-donations'
										),
										mode + '_'
									),
								},
							}}
							type={isDisabled ? 'password' : 'text'}
							label={
								<span className="capitalize">{mode} key</span>
							}
						/>
					);
				})}
				<button
					type="button"
					className="ml-auto text-red-600 underline text-right cursor-pointer block"
					disabled={checkingApiKey}
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
				</button>
			</Panel>
		</Fragment>
	);
};

export default MollieTab;
