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

export const Brevo = ({ refresh, isBusy }) => {
	const { settings, updateSettings } = useSettingsContext();
	return (
		<>
			<Panel header={__('API', 'kudos-donations')}>
				<PanelBody>
					<TextControl
						prefix={<Icon icon="shield" />}
						label={__('API Key', 'kudos-donations')}
						name="_kudos_brevo_api_key"
						help={__(
							'Enter your Brevo api key here.',
							'kudos-donations'
						)}
					/>
					<Flex justify="space-between">
						<ExternalLink href="https://app.brevo.com/settings/keys/api">
							{__('Visit Brevo dashboard', 'kudos-donations')}.
						</ExternalLink>
						<Button
							type="button"
							variant="link"
							isDestructive={true}
							onClick={() => {
								updateSettings({
									_kudos_brevo_api_key: '',
									_kudos_brevo_lists: [],
									_kudos_brevo_selected_list: '',
								});
							}}
						>
							{__('Reset Brevo', 'kudos-donations')}
						</Button>
					</Flex>
				</PanelBody>
			</Panel>
			<Panel header={__('Brevo settings', 'kudos-donations')}>
				<PanelBody>
					<SelectControl
						label={__('List', 'kudos-donations')}
						name="_kudos_brevo_selected_list"
						options={settings._kudos_brevo_lists
							?.map((list) => {
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
