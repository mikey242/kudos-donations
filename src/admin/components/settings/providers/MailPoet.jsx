import { Button, Panel, PanelBody } from '@wordpress/components';
import { SelectControl } from '../../controls';
import { __ } from '@wordpress/i18n';
import { useSettingsContext } from '../../../contexts/SettingsContext';

export const MailPoet = ({ refresh, isBusy }) => {
	const { settings } = useSettingsContext();
	return (
		<>
			<Panel header={__('MailPoet settings', 'kudos-donations')}>
				<PanelBody>
					<SelectControl
						label={__('List', 'kudos-donations')}
						name="_kudos_mailpoet_selected_list"
						options={settings._kudos_mailpoet_lists
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
						{__('Refresh Lists', 'kudos-donations')}
					</Button>
				</PanelBody>
			</Panel>
		</>
	);
};
