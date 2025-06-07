import intro from '../../../assets/images/full-logo-green.png';
import mollie from '../../../assets/images/guide-mollie-api.png';
import campaign from '../../../assets/images/guide-campaign.png';
import button from '../../../assets/images/guide-button.png';
import live from '../../../assets/images/guide-test-live.png';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { useSettingsContext } from '../contexts';
import { ExternalLink, Guide } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';

const IntroGuide = (): React.ReactNode => {
	const { updateSetting, settings } = useSettingsContext();
	const [showGuide, setShowGuide] = useState<boolean>(false);

	useEffect(() => {
		setShowGuide(settings._kudos_show_intro);
	}, [settings._kudos_show_intro]);

	const closeModal = () => {
		void updateSetting('_kudos_show_intro', 0);
		setShowGuide(false);
	};

	if (!showGuide) {
		return null;
	}

	return (
		<Guide
			onFinish={closeModal}
			className="kudos-intro-guide"
			pages={[
				{
					image: <img width="100%" src={intro} alt="intro" />,
					content: (
						<>
							<h1 className="kudos-intro-guide-header">
								{__(
									'Welcome to Kudos Donations',
									'kudos-donations'
								)}
							</h1>
							<p className="kudos-intro-guide-text">
								{__(
									'Complete these simple steps to set up your first donation campaign. Click the "next" button to get started',
									'kudos-donations'
								)}
							</p>
						</>
					),
				},
				{
					image: <img src={mollie} alt="mollie" />,
					content: (
						<>
							<h1 className="kudos-intro-guide-header">
								{__('Connect with Mollie', 'kudos-donations')}
							</h1>
							<div className="kudos-intro-guide-text">
								<p>
									{__(
										'Login to your Mollie account and grab your API keys. Then visit the settings page and enter them in the Mollie section.',
										'kudos-donations'
									)}
								</p>
								<ExternalLink href="https://my.mollie.com/dashboard/developers/api-keys">
									{__(
										'Visit Mollie dashboard',
										'kudos-donations'
									)}
									.
								</ExternalLink>
							</div>
						</>
					),
				},
				{
					image: <img src={campaign} alt="campaign" />,
					content: (
						<>
							<h1 className="kudos-intro-guide-header">
								{__('Set up a campaign', 'kudos-donations')}
							</h1>
							<div className="kudos-intro-guide-text">
								<p>
									{__(
										'Visit the campaigns page and click the "+ New campaign" button to create your first campaign. Then click the edit button to start customising it.',
										'kudos-donations'
									)}
								</p>
							</div>
						</>
					),
				},
				{
					image: <img src={button} alt="button" />,
					content: (
						<>
							<h1 className="kudos-intro-guide-header">
								{__('Place a form', 'kudos-donations')}
							</h1>
							<div className="kudos-intro-guide-text">
								<p>
									{__(
										'Use the Kudos Donations block or shortcode to place the button anywhere on your website.',
										'kudos-donations'
									)}
								</p>
							</div>
						</>
					),
				},
				{
					image: <img src={live} alt="live" />,
					content: (
						<>
							<h1 className="kudos-intro-guide-header">
								{__('Test and go Live', 'kudos-donations')}
							</h1>
							<p className="kudos-intro-guide-text">
								{__(
									'With the API mode still on "Test" make a payment using Kudos Donations. If it worked then you can switch to "Live".',
									'kudos-donations'
								)}
							</p>
						</>
					),
				},
			]}
			contentLabel={__('Intro guide', 'kudos-donations')}
		/>
	);
};

export { IntroGuide };
