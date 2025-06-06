import { __ } from '@wordpress/i18n';
import logo from '../../../../../assets/images/full-logo-green.svg';
import React from 'react';
import { useSettingsContext } from '../../../contexts';
import { Button, Flex, FlexItem } from '@wordpress/components';
import { TextControl, ToggleControl } from '../../controls';
import { Panel } from '../../Panel';

const HelpTab = (): React.ReactNode => {
	const { updateSetting } = useSettingsContext();

	return (
		<>
			<Panel header={__('Share the love', 'kudos-donations')}>
				<p className="mb-2">
					{__(
						'Do you like using Kudos? Please let us know your thoughts.',
						'kudos-donations'
					)}
				</p>
				<Flex justify="flex-start">
					<Button
						variant="secondary"
						icon="edit"
						href="https://wordpress.org/support/plugin/kudos-donations/reviews/#new-post"
						target="_blank"
						rel="noreferrer"
					>
						{__('Leave a review', 'kudos-donations')}
					</Button>
				</Flex>
			</Panel>
			<Panel header={__('Need some assistance?', 'kudos-donations')}>
				<p>
					{__(
						"Don't hesitate to get in touch if you need any help or have a suggestion.",
						'kudos-donations'
					)}
				</p>
				<Flex>
					<FlexItem>
						<Flex justify="flex-start">
							<Button
								variant="secondary"
								icon="groups"
								href="https://wordpress.org/support/plugin/kudos-donations/"
								target="_blank"
								rel="noreferrer"
							>
								{__('Support forums', 'kudos-donations')}
							</Button>
							<Button
								variant="secondary"
								icon="info"
								onClick={() =>
									updateSetting('_kudos_show_intro', true)
								}
							>
								{__('Show welcome guide', 'kudos-donations')}
							</Button>
							<Button
								variant="secondary"
								icon="editor-help"
								href="https://docs.kudosdonations.com/"
								target="_blank"
								rel="noreferrer"
							>
								{__(
									'Visit our Documentation',
									'kudos-donations'
								)}
							</Button>
						</Flex>
					</FlexItem>
					<FlexItem>
						<Button
							target="_blank"
							rel="noreferrer"
							variant="link"
							title={__(
								'Visit Kudos Donations',
								'kudos-donations'
							)}
							aria-label={__(
								'Visit Kudos Donations',
								'kudos-donations'
							)}
							className="block"
							href="https://kudosdonations.com"
						>
							<img
								width="140"
								src={logo}
								className="mr-4"
								alt="Kudos Logo"
							/>
						</Button>
					</FlexItem>
				</Flex>
			</Panel>
			<Panel header={__('Advanced', 'kudos-donations')}>
				<ToggleControl
					name="_kudos_always_load_assets"
					label={__('Always load assets', 'kudos-donations')}
					help={__(
						'This will ensure that the JavaScript for Kudos is loaded on every page.',
						'kudos-donations'
					)}
				/>
				<ToggleControl
					name="_kudos_debug_mode"
					label={__('Debug mode', 'kudos-donations')}
					help={__(
						'Enables debug logging. Please only enable this if advised to by support.',
						'kudos-donations'
					)}
				/>
				<TextControl
					name="_kudos_base_font_size"
					label={__('Base font size', 'kudos-donations')}
					help={__(
						"This will change the scale of all font sizes and spacing within the Kudos Donations frame. The default is 1rem which is your website's main font size.",
						'kudos-donations'
					)}
				/>
			</Panel>
		</>
	);
};

export { HelpTab };
