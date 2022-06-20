import intro from '../../images/guide-welcome.png';
import mollie from '../../images/guide-mollie-api.png';
import campaign from '../../images/guide-campaign.png';
import button from '../../images/guide-button.png';
import live from '../../images/guide-test-live.png';
import { __ } from '@wordpress/i18n';
import { ExternalLink } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { Guide } from './Guide';
import React from 'react';
import { Button, TextControl } from '../../common/components/controls';
import { useFormContext } from 'react-hook-form';
import Panel from './Panel';

const IntroGuide = ({
	settings,
	updateSetting,
	isAPISaving,
	setShowIntro,
	checkApiKey,
}) => {
	const [apiMessage, setApiMessage] = useState(null);
	const vendorMollie = settings._kudos_vendor_mollie;
	const isConnected = vendorMollie.connected ?? false;
	const isRecurringEnabled = vendorMollie.recurring ?? false;
	const { formState } = useFormContext();

	const closeModal = () => {
		setShowIntro(false);
		updateSetting('_kudos_show_intro', false);
	};

	useEffect(() => {
		if (formState.isSubmitted) {
			checkApiKey();
		}
	}, [formState.isSubmitted]);

	return (
		<Guide
			className={'box-border'}
			onFinish={closeModal}
			pages={[
				{
					imageSrc: intro,
					heading: __(
						'Welcome to Kudos Donations',
						'kudos-donations'
					),
					content: (
						<div className={'text-center'}>
							<p>
								{__(
									'Complete these simple steps to set up your first donation campaign. Click the "next" button to get started',
									'kudos-donations'
								)}
							</p>
						</div>
					),
				},
				{
					imageSrc: mollie,
					nextDisabled: !vendorMollie.connected,
					heading: __('Connect with Mollie', 'kudos-donations'),
					content: (
						<div className={'text-center'}>
							{!isConnected ? (
								<>
									<p>
										{__(
											'Time to connect with Mollie. Login to your Mollie account and grab your API keys. Make sure you get both your test and live API keys.',
											'kudos-donations'
										)}{' '}
									</p>
									<a
										className="text-primary"
										target="_blank"
										href="https://mollie.com/dashboard/developers/api-keys"
										rel="noreferrer"
									>
										{__(
											'Mollie dashboard',
											'kudos-donations'
										)}
									</a>
									<Panel className="p-5">
										<TextControl
											name={
												'_kudos_vendor_mollie.live_key'
											}
											disabled={isAPISaving}
											label={__(
												'Live key',
												'kudos-donations'
											)}
											placeholder={__(
												'Begins with "live_"',
												'kudos-donations'
											)}
										/>
										<TextControl
											name={
												'_kudos_vendor_mollie.test_key'
											}
											disabled={isAPISaving}
											label={__(
												'Test key',
												'kudos-donations'
											)}
											placeholder={__(
												'Begins with "test_"',
												'kudos-donations'
											)}
										/>
									</Panel>
									<br />
									<Button
										isOutline
										type="submit"
										isDisabled={isAPISaving}
										// onClick={() => checkApi()}
									>
										{__('Connect', 'kudos-donations')}
									</Button>
									<div
										className="mt-3 text-base"
										style={{
											color: 'red',
										}}
									>
										{apiMessage}
									</div>
								</>
							) : (
								<div className="flex flex-col rounded-lg p-5">
									<div
										className={
											'flex flex-row justify-center mb-3 items-center'
										}
									>
										<h2 className={'m-0 text-green-500'}>
											{__('Connected', 'kudos-donations')}{' '}
											(
											{isRecurringEnabled
												? __(
														'recurring enabled',
														'kudos-donations'
												  )
												: __(
														'recurring not available',
														'kudos-donations'
												  )}
											)
										</h2>
									</div>
									{isRecurringEnabled ? (
										<strong>
											{__(
												'Congratulations, your account is configured to allow recurring payments.',
												'kudos-donations'
											)}
										</strong>
									) : (
										<strong>
											{__(
												'You can still use Kudos, however you will not be able to use subscription payments.',
												'kudos-donations'
											)}
										</strong>
									)}
									<a
										className={'text-primary mt-2'}
										href={
											'https://help.mollie.com/hc/articles/214558045'
										}
									>
										{__('Learn more', 'kudos-donations')}
									</a>
								</div>
							)}
						</div>
					),
				},
				{
					imageSrc: campaign,
					heading: __('Set up a campaign', 'kudos-donations'),
					content: (
						<div className={'text-center'}>
							<p>
								{__(
									'Visit the campaigns tab and either create a new campaign or edit the default one.',
									'kudos-donations'
								)}
							</p>
							<p>
								{__(
									'If you need it, don\'t forget to click "Copy shortcode" at the bottom of your campaign.',
									'kudos-donations'
								)}
							</p>
						</div>
					),
				},
				{
					imageSrc: button,
					heading: __('Place a button', 'kudos-donations'),
					content: (
						<div className={'text-center'}>
							<p>
								{__(
									'Use the Kudos Button block or shortcode to place the button anywhere on your website.',
									'kudos-donations'
								)}
							</p>
							<p>
								{__(
									'If using the block, select the desired campaign in the block side bar.',
									'kudos-donations'
								)}
							</p>
						</div>
					),
				},
				{
					imageSrc: live,
					heading: __('Test and go Live', 'kudos-donations'),
					content: (
						<div className={'text-center'}>
							<p>
								{__(
									'With the API mode still on "Test" make a payment using Kudos Donations. If it worked then you can switch to "Live".',
									'kudos-donations'
								)}
							</p>
							<p>
								{__(
									'Good luck with your campaign!',
									'kudos-donations'
								)}
							</p>
							<p>
								<ExternalLink href="https://kudosdonations.com/faq/">
									{__('Visit our F.A.Q', 'kudos-donations')}
								</ExternalLink>
							</p>
						</div>
					),
				},
			]}
		/>
	);
};

export { IntroGuide };
