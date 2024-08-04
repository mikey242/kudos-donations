import { __ } from '@wordpress/i18n';
import React from 'react';
import { Button, RadioGroupControl, TextControl } from '../../common/controls';
import { Fragment } from '@wordpress/element';
import Divider from '../../common/Divider';
import { clsx } from 'clsx';
import { useSettingsContext } from '../../common/contexts/SettingsContext';
import { ArrowPathIcon, CheckCircleIcon } from '@heroicons/react/24/outline';
import { useFormContext } from 'react-hook-form';
import { Panel } from '../../common/Panel';
import { ArrowTopRightOnSquareIcon } from '@heroicons/react/16/solid';

const MollieTab = ({ checkApiKeys }) => {
	const { checkingApiKey, updateSetting } = useSettingsContext();
	const { getValues, watch } = useFormContext();
	const watchLive = watch('_kudos_vendor_mollie.live_key');
	const watchTest = watch('_kudos_vendor_mollie.test_key');
	const live = getValues('_kudos_vendor_mollie.live_key');
	const test = getValues('_kudos_vendor_mollie.test_key');
	const refresh = () => {
		const keys = {
			...(live?.key && { live: live.key }),
			...(test?.key && { test: test.key }),
		};
		checkApiKeys(keys);
	};

	const checkKey = (name, key) => {
		if (key) {
			checkApiKeys({ [name]: key });
		}
	};

	return (
		<Fragment>
			<Panel title={__('API Mode', 'kudos-donations')}>
				<RadioGroupControl
					name="_kudos_vendor_mollie.mode"
					label={__('API Mode', 'kudos-donations')}
					hideLabel={true}
					options={[
						{
							label: __('Test', 'kudos-donations'),
							value: 'test',
							disabled: !test?.verified,
						},
						{
							label: __('Live', 'kudos-donations'),
							value: 'live',
							disabled: !live?.verified,
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
				{[
					{ ...watchLive, name: 'live' },
					{ ...watchTest, name: 'test' },
				].map((mode, i) => {
					return (
						<TextControl
							key={i}
							name={`_kudos_vendor_mollie.${mode?.name}_key.key`}
							isDisabled={checkingApiKey}
							isReadOnly={mode?.verified}
							type={mode?.verified ? 'password' : 'text'}
							label={
								<span className="capitalize">
									{' '}
									{mode?.name} key
								</span>
							}
							inlineButton={
								mode?.verified ? (
									<CheckCircleIcon
										className={'text-green-600 w-6 h-6'}
									/>
								) : (
									<Button
										isOutline
										onClick={() =>
											checkKey(mode.name, mode?.key)
										}
									>
										{__('Apply', 'kudos-donations')}
									</Button>
								)
							}
						/>
					);
				})}
				<button
					type="button"
					className="ml-auto text-red-600 underline text-right cursor-pointer block"
					disabled={checkingApiKey}
					onClick={() =>
						updateSetting('_kudos_vendor_mollie', { mode: 'test' })
					}
				>
					{__('Reset Mollie', 'kudos-donations')}
				</button>
			</Panel>
		</Fragment>
	);
};

export default MollieTab;
