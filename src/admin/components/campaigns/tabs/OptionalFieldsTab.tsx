import { __ } from '@wordpress/i18n';
import { TextControl, ToggleControl, CheckboxControl } from '../../controls';
import React from 'react';
import { useWatch } from 'react-hook-form';
import { Panel, PanelRow } from '../../Panel';

export const OptionalFieldsTab = () => {
	const watchAddress = useWatch({ name: 'meta.address_enabled' });
	const watchName = useWatch({ name: 'meta.name_enabled' });
	const watchEmail = useWatch({ name: 'meta.email_enabled' });
	const watchMessage = useWatch({ name: 'meta.message_enabled' });
	const watchType = useWatch({ name: 'meta.donation_type' });

	return (
		<>
			<Panel header={__('Optional fields', 'kudos-donations')}>
				<PanelRow>
					<ToggleControl
						name="meta.email_enabled"
						isDisabled={watchType !== 'oneoff'}
						label={__('Email address', 'kudos-donations')}
						help={__(
							'Show the email address field. This can only be disabled if one-off donations are selected',
							'kudos-donations'
						)}
					/>
					<CheckboxControl
						name="meta.email_required"
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
						name="meta.name_enabled"
						label={__('Name', 'kudos-donations')}
						help={__('Show the name field.', 'kudos-donations')}
					/>
					<CheckboxControl
						name="meta.name_required"
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
						name="meta.address_enabled"
						label={__('Address', 'kudos-donations')}
						help={__('Show the address tab.', 'kudos-donations')}
					/>

					<CheckboxControl
						name="meta.address_required"
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
						name="meta.message_enabled"
						label={__('Message', 'kudos-donations')}
						help={__(
							'Allow donors to leave a message.',
							'kudos-donations'
						)}
					/>
					<CheckboxControl
						name="meta.message_required"
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
					name="meta.terms_link"
					label={__('Terms & Conditions URL', 'kudos-donations')}
					help={__(
						'Add a URL to your Terms & Conditions, donors will need to agree to them before donating.',
						'kudos-donations'
					)}
				/>
				<TextControl
					name="meta.privacy_link"
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
