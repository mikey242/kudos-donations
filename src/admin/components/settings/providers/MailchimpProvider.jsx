import {
	Button,
	ExternalLink,
	Flex,
	Icon,
	Panel,
	PanelBody,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { SelectControl, TextControl } from '../../controls';
import React from 'react';
import { useSettingsContext } from '../../../contexts/SettingsContext';

export const MailchimpProvider = ({ refresh, isBusy }) => {
	const { settings, updateSettings } = useSettingsContext();
	return (
		<>
			<Panel header={__('API', 'kudos-donations')}>
				<PanelBody>
					<TextControl
						prefix={<Icon icon="shield" />}
						label={__('API Key', 'kudos-donations')}
						name="_kudos_mailchimp_api_key"
						help={__(
							'Enter your Mailchimp api key here.',
							'kudos-donations'
						)}
					/>
					<Flex justify="space-between">
						<ExternalLink href="https://admin.mailchimp.com/account/api/">
							{__('Visit Mailchimp dashboard', 'kudos-donations')}
							.
						</ExternalLink>
						<Button
							type="button"
							variant="link"
							isDestructive={true}
							onClick={() => {
								updateSettings({
									_kudos_mailchimp_api_key: '',
									_kudos_mailchimp_audiences: [],
									_kudos_mailchimp_selected_audience: '',
								});
							}}
						>
							{__('Reset Mailchimp', 'kudos-donations')}
						</Button>
					</Flex>
				</PanelBody>
			</Panel>
			<Panel header={__('Mailchimp settings', 'kudos-donations')}>
				<PanelBody>
					<SelectControl
						label={__('Audience', 'kudos-donations')}
						name="_kudos_mailchimp_selected_audience"
						options={settings._kudos_mailchimp_audiences
							.map((list) => {
								return {
									label: list.name,
									value: list.id,
								};
							})
							.concat({
								label: '',
								value: '',
								disabled: true,
							})}
					/>
					<TextControl
						label={__('Tag', 'kudos-donations')}
						name="_kudos_mailchimp_contact_tag"
					/>
					<Button
						type="button"
						variant="link"
						isBusy={isBusy}
						disabled={isBusy}
						onClick={() => refresh()}
					>
						{__('Refresh Audiences', 'kudos-donations')}
					</Button>
				</PanelBody>
			</Panel>
		</>
	);
};
