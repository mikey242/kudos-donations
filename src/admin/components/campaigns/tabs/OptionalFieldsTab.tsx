import { __ } from '@wordpress/i18n';
import { TextControl, ToggleControl, CheckboxControl } from '../../controls';
import React from 'react';
import { useWatch } from 'react-hook-form';
import { Panel } from '../../Panel';

export const OptionalFieldsTab = () => {
	const watchAddress = useWatch({ name: 'meta.address_enabled' });

	return (
		<>
			<Panel header={__('Optional fields', 'kudos-donations')}>
				<ToggleControl
					name="meta.allow_anonymous"
					label={__('Allow anonymous donations', 'kudos-donations')}
					help={__(
						'Allow users to donate without leaving a name or email address. Anonymous users can only perform one-off donations.',
						'kudos-donations'
					)}
				/>
				<ToggleControl
					name="meta.address_enabled"
					label={__('Address', 'kudos-donations')}
					help={__('Show the address tab.', 'kudos-donations')}
				/>
				<CheckboxControl
					name="meta.address_required"
					isDisabled={!watchAddress}
					help={__('Make the address required.', 'kudos-donations')}
					label={__('Required', 'kudos-donations')}
				/>
				<ToggleControl
					name="meta.message_enabled"
					label={__('Message', 'kudos-donations')}
					help={__(
						'Allow donors to leave a message.',
						'kudos-donations'
					)}
				/>
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
