import { __ } from '@wordpress/i18n';
import { TextControl, ToggleControl, CheckboxControl } from '../../controls';
import React from 'react';
import { useWatch } from 'react-hook-form';
import { Panel, PanelRow } from '../../Panel';

export const OptionalFieldsTab = () => {
	const watchAddress = useWatch({ name: 'address_enabled' });
	const watchName = useWatch({ name: 'name_enabled' });
	const watchEmail = useWatch({ name: 'email_enabled' });
	const watchMessage = useWatch({ name: 'message_enabled' });
	const watchType = useWatch({ name: 'donation_type' });

	return (
		<>
			<Panel header={__('Optional fields', 'kudos-donations')}>
				<PanelRow>
					<ToggleControl
						name="email_enabled"
						isDisabled={watchType !== 'oneoff'}
						label={__('Email address', 'kudos-donations')}
						help={__(
							'Show the email address field. This can only be disabled if one-off donations are selected',
							'kudos-donations'
						)}
					/>
					<CheckboxControl
						name="email_required"
						isDisabled={!watchEmail || watchType !== 'oneoff'}
						help={__(
							'Allow users to donate without leaving an email address. This can only be disabled if one-off donations are selected.',
							'kudos-donations'
						)}
						label={__('Required', 'kudos-donations')}
					/>
				</PanelRow>
				<PanelRow>
					<ToggleControl
						name="name_enabled"
						label={__('Name', 'kudos-donations')}
						help={__('Show the name field.', 'kudos-donations')}
					/>
					<CheckboxControl
						name="name_required"
						isDisabled={!watchName}
						help={__(
							'Make the name field required.',
							'kudos-donations'
						)}
						label={__('Required', 'kudos-donations')}
					/>
				</PanelRow>
				<PanelRow>
					<ToggleControl
						name="address_enabled"
						label={__('Address', 'kudos-donations')}
						help={__('Show the address tab.', 'kudos-donations')}
					/>

					<CheckboxControl
						name="address_required"
						isDisabled={!watchAddress}
						help={__(
							'Make the address required.',
							'kudos-donations'
						)}
						label={__('Required', 'kudos-donations')}
					/>
				</PanelRow>
				<PanelRow>
					<ToggleControl
						name="message_enabled"
						label={__('Message', 'kudos-donations')}
						help={__(
							'Allow donors to leave a message.',
							'kudos-donations'
						)}
					/>
					<CheckboxControl
						name="message_required"
						isDisabled={!watchMessage}
						help={__(
							'Make the message field required.',
							'kudos-donations'
						)}
						label={__('Required', 'kudos-donations')}
					/>
				</PanelRow>
			</Panel>
			<Panel header={__('Policy links', 'kudos-donations')}>
				<TextControl
					name="terms_link"
					label={__('Terms & Conditions URL', 'kudos-donations')}
					help={__(
						'Add a URL to your Terms & Conditions, donors will need to agree to them before donating.',
						'kudos-donations'
					)}
				/>
				<TextControl
					name="privacy_link"
					label={__('Privacy Policy URL', 'kudos-donations')}
					help={__(
						'Add a URL to your Privacy policy, donors will need to agree to it before donating.',
						'kudos-donations'
					)}
				/>
			</Panel>
		</>
	);
};
