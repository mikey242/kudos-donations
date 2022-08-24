import intro from '../../images/guide-welcome.png';
import mollie from '../../images/guide-mollie-api.png';
import campaign from '../../images/guide-campaign.png';
import button from '../../images/guide-button.png';
import live from '../../images/guide-test-live.png';
import { __ } from '@wordpress/i18n';
import { Guide } from '../../common/components/Guide';
import React from 'react';
import { Newsletter } from './Newsletter';
import KudosModal from '../../common/components/KudosModal';
import { FormProvider, useForm } from 'react-hook-form';
import { Button, TextControl } from '../../common/components/controls';
import { useState } from '@wordpress/element';
import { useSettingsContext } from '../contexts/SettingsContext';

const IntroGuide = ({ setShowIntro, isOpen }) => {
	const { updateSetting, checkApiKey, settings } = useSettingsContext();
	const vendorMollie = settings._kudos_vendor_mollie;
	const isConnected = vendorMollie.connected ?? false;
	const isRecurringEnabled = vendorMollie.recurring ?? false;

	const [isApiSaving, setIsApiSaving] = useState(false);
	const [apiMessage, setApiMessage] = useState(null);

	const methods = useForm();

	const submitMollie = (data) => {
		console.log(data);
		setIsApiSaving(true);
		checkApiKey({
			keys: data.keys,
		})
			.then((response) => {
				setApiMessage(response.data.message);
			})
			.catch((error) => {
				setApiMessage(error.message);
			})
			.finally(() => {
				setIsApiSaving(false);
			});
	};

	const closeModal = () => {
		setShowIntro(false);
		updateSetting('_kudos_show_intro', false);
	};

	return (
		<KudosModal isOpen={isOpen} toggleModal={closeModal}>
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
							<p>
								{__(
									'Complete these simple steps to set up your first donation campaign. Click the "next" button to get started',
									'kudos-donations'
								)}
							</p>
						),
					},
					{
						imageSrc: mollie,
						nextDisabled: !vendorMollie.connected,
						heading: __('Connect with Mollie', 'kudos-donations'),
						content: (
							<>
								{!isConnected ? (
									<div className="mb-2">
										<p>
											{__(
												'Login to your Mollie account and grab your API keys.',
												'kudos-donations'
											)}{' '}
											<a
												className="text-primary inline"
												target="_blank"
												href="https://mollie.com/dashboard/developers/api-keys"
												rel="noreferrer"
											>
												{__(
													'Visit Mollie dashboard',
													'kudos-donations'
												)}
												.
											</a>
										</p>
										<FormProvider {...methods}>
											<form
												className="mt-5"
												onSubmit={methods.handleSubmit(
													submitMollie
												)}
											>
												{apiMessage && (
													<div className="text-sm text-red-500">
														{apiMessage}
													</div>
												)}
												<TextControl
													name="keys.live_key"
													disabled={isApiSaving}
													placeholder={__(
														'Live key',
														'kudos-donations'
													)}
												/>
												<TextControl
													name="keys.test_key"
													disabled={isApiSaving}
													placeholder={__(
														'Test key',
														'kudos-donations'
													)}
												/>
												<div className="mt-3 flex justify-end relative">
													<Button
														isSmall
														isBusy={isApiSaving}
														type="submit"
														className="w-full"
													>
														{__(
															'Connect',
															'kudos-donations'
														)}
													</Button>
												</div>
											</form>
										</FormProvider>
									</div>
								) : (
									<div className="flex flex-col rounded-lg">
										<div
											className={
												'flex flex-row justify-center mb-3 items-center'
											}
										>
											<h2
												className={'m-0 text-green-500'}
											>
												{__(
													'Connected',
													'kudos-donations'
												)}{' '}
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
											<p>
												{__(
													'Congratulations, your account is configured to allow recurring payments.',
													'kudos-donations'
												)}
											</p>
										) : (
											<p>
												{__(
													'You can still use Kudos, however you will not be able to use subscription payments.',
													'kudos-donations'
												)}
											</p>
										)}
										<a
											className={'text-primary mt-2'}
											target="_blank"
											href={
												'https://help.mollie.com/hc/articles/214558045'
											}
											rel="noreferrer"
										>
											{__(
												'Learn more',
												'kudos-donations'
											)}
										</a>
									</div>
								)}
							</>
						),
					},
					{
						imageSrc: campaign,
						heading: __('Set up a campaign', 'kudos-donations'),
						content: (
							<>
								<p>
									{__(
										'Visit the campaigns page and either create a new campaign or edit the default one.',
										'kudos-donations'
									)}
								</p>
								<p>
									{__(
										'If you need it, don\'t forget to click "Copy shortcode" at the bottom of your campaign.',
										'kudos-donations'
									)}
								</p>
							</>
						),
					},
					{
						imageSrc: button,
						heading: __('Place a button', 'kudos-donations'),
						content: (
							<>
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
							</>
						),
					},
					{
						imageSrc: live,
						heading: __('Test and go Live', 'kudos-donations'),
						content: (
							<>
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
									<a
										className="text-primary"
										href="src/admin/components/guide/IntroGuide"
										target="_blank"
										rel="noreferrer"
									>
										{__(
											'Visit our F.A.Q',
											'kudos-donations'
										)}
									</a>
								</p>
							</>
						),
					},
					{
						imageSrc: intro,
						heading: __(
							'Sign-up for our newsletter',
							'kudos-donations'
						),
						content: <Newsletter />,
					},
				]}
			/>
		</KudosModal>
	);
};

export { IntroGuide };
