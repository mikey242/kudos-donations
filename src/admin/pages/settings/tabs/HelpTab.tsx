import { __ } from '@wordpress/i18n';
import type { AdminTab } from '../../AdminTabPanel';
import { Button, Flex } from '@wordpress/components';
import { TextControl, ToggleControl } from '../../../controls';
import { Panel } from '../../../components';
import { LogModal } from '../../LogModal';
import React from 'react';

const ShareTheLovePanel = () => (
	<Panel header={__('Share the love', 'kudos-donations')} spacing={2}>
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
);

const AboutPanel = () => {
	return (
		<Panel
			header={__('About', 'kudos-donations')}
			spacing={2}
			headerExtra={
				<>
					<span>
						<strong>Plugin Version: </strong>
						{window.kudos.version?.base}
					</span>
				</>
			}
		>
			<p>
				Kudos Donations developed by Michael Iseard /{' '}
				<a href="https://iseard.media">Iseard Media</a>
			</p>
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
					icon="editor-help"
					href="https://docs.kudosdonations.com/"
					target="_blank"
					rel="noreferrer"
				>
					{__('Visit our Documentation', 'kudos-donations')}
				</Button>
				<LogModal />
			</Flex>
		</Panel>
	);
};

const AdvancedPanel = () => (
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
);

export const HelpTab: AdminTab = {
	name: 'help',
	title: __('Help', 'kudos-donations'),
	panels: [
		{ name: 'share-the-love', content: <ShareTheLovePanel /> },
		{ name: 'about', content: <AboutPanel /> },
		{ name: 'advanced', content: <AdvancedPanel /> },
	],
};
