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

export const MailerLite = ({ refresh, isBusy }) => {
	const { settings, updateSettings } = useSettingsContext();
	return (
		<>
			<Panel header={__('API', 'kudos-donations')}>
				<PanelBody>
					<TextControl
						prefix={<Icon icon="shield" />}
						label={__('API Key', 'kudos-donations')}
						name="_kudos_mailerlite_api_key"
						help={__(
							'Enter your Mailerlite api key here.',
							'kudos-donations'
						)}
					/>
					<Flex justify="space-between">
						<ExternalLink href="https://dashboard.mailerlite.com/integrations/api">
							{__(
								'Visit Mailerlite dashboard',
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
									_kudos_mailerlite_api_key: '',
									_kudos_mailerlite_groups: [],
									_kudos_mailerlite_selected_group: '',
								});
							}}
						>
							{__('Reset Mailerlite', 'kudos-donations')}
						</Button>
					</Flex>
				</PanelBody>
			</Panel>
			<Panel header={__('Mailerlite settings', 'kudos-donations')}>
				<PanelBody>
					<SelectControl
						label={__('Group', 'kudos-donations')}
						name="_kudos_mailerlite_selected_group"
						options={settings._kudos_mailerlite_groups
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
					<Button
						type="button"
						variant="link"
						isBusy={isBusy}
						disabled={isBusy}
						onClick={() => refresh()}
					>
						{__('Refresh Groups', 'kudos-donations')}
					</Button>
				</PanelBody>
			</Panel>
		</>
	);
};
