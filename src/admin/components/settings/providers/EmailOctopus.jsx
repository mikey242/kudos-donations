import {
	Button,
	ExternalLink,
	Flex,
	Icon,
	Panel,
	PanelBody,
} from '@wordpress/components';
import { SelectControl, TextControl } from '../../controls';
import { __ } from '@wordpress/i18n';
import { useSettingsContext } from '../../../contexts/SettingsContext';
import React from 'react';

export const EmailOctopus = ({ refresh, isBusy }) => {
	const { settings, updateSettings } = useSettingsContext();
	return (
		<>
			<Panel header={__('API', 'kudos-donations')}>
				<PanelBody>
					<TextControl
						prefix={<Icon icon="shield" />}
						label={__('API Key', 'kudos-donations')}
						name="_kudos_emailoctopus_api_key"
						help={__(
							'Enter your EmailOctopus api key here.',
							'kudos-donations'
						)}
					/>
					<Flex justify="space-between">
						<ExternalLink href="https://emailoctopus.com/developer/api-keys">
							{__(
								'Visit EmailOctopus dashboard',
								'kudos-donations'
							)}
							.
						</ExternalLink>
						<Button
							type="button"
							variant="link"
							isDestructive={true}
							onClick={() => {
								updateSettings({
									_kudos_emailoctopus_api_key: '',
									_kudos_emailoctopus_groups: [],
									_kudos_emailoctopus_selected_group: '',
								});
							}}
						>
							{__('Reset EmailOctopus', 'kudos-donations')}
						</Button>
					</Flex>
				</PanelBody>
			</Panel>
			<Panel header={__('EmailOctopus settings', 'kudos-donations')}>
				<PanelBody>
					<SelectControl
						label={__('Lists', 'kudos-donations')}
						name="_kudos_emailoctopus_selected_list"
						options={settings._kudos_emailoctopus_lists
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
						name="_kudos_emailoctopus_tag"
					/>
					<Button
						type="button"
						variant="link"
						isBusy={isBusy}
						disabled={isBusy}
						onClick={() => refresh()}
					>
						{__('Refresh Lists', 'kudos-donations')}
					</Button>
				</PanelBody>
			</Panel>
		</>
	);
};
