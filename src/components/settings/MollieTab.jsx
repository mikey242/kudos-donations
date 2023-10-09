import { __ } from '@wordpress/i18n';
import React from 'react';
import { Button, RadioGroupControl, TextControl } from '../controls';
import { Fragment, useState } from '@wordpress/element';
import Divider from '../Divider';
import classNames from 'classnames';
import { useSettingsContext } from '../../contexts/SettingsContext';
import { ArrowPathIcon, CheckCircleIcon } from '@heroicons/react/24/outline';
import { useFormContext } from 'react-hook-form';

const MollieTab = ({ checkApiKeys }) => {
	const [checkingKey, setCheckingKey] = useState({});
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
			setCheckingKey({
				[name]: true,
			});
			checkApiKeys({ [name]: key }).then(() =>
				setCheckingKey({ [name]: false })
			);
		}
	};

	return (
		<Fragment>
			<RadioGroupControl
				name="_kudos_vendor_mollie.mode"
				label={__('API Mode', 'kudos-donations')}
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
							className={classNames(
								checkingApiKey && 'animate-spin',
								'w-5 h-5'
							)}
						/>{' '}
						<span className="mx-2">
							{__('Refresh API', 'kudos-donations')}
						</span>
					</>
				</button>
				<p className="my-2 text-sm text-gray-500">
					{__(
						'Use this if you have made changes in Mollie such as enabling SEPA Direct Debit or credit card.',
						'kudos-donations'
					)}
				</p>
			</div>
			<Divider />
			<p>You can get your Mollie API keys <a target="_blank" className="underline" href="https://my.mollie.com/dashboard/developers/api-keys">here</a>.</p>
			<>
				{[
					{ ...watchLive, name: 'live' },
					{ ...watchTest, name: 'test' },
				].map((mode, i) => {
					return (
						<TextControl
							key={i}
							name={`_kudos_vendor_mollie.${mode?.name}_key.key`}
							disabled={mode?.verified || checkingKey[mode.name]}
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
					onClick={() =>
						updateSetting('_kudos_vendor_mollie', { mode: 'test' })
					}
				>
					{__('Reset Mollie', 'kudos-donations')}
				</button>
			</>
		</Fragment>
	);
};

export default MollieTab;
