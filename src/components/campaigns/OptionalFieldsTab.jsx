import { Fragment } from '@wordpress/element';
import { Panel } from '../Panel';
import { __ } from '@wordpress/i18n';
import { CheckboxControl, TextControl, ToggleControl } from '../controls';
import Divider from '../Divider';
import React from 'react';
import { useWatch } from 'react-hook-form';

export const OptionalFieldsTab = () => {
	const watchAddress = useWatch({ name: 'meta.address_enabled' });

	return (
		<Fragment>
			<Panel title={__('Optional fields', 'kudos-donations')}>
				<ToggleControl
					name="meta.allow_anonymous"
					label={__('Allow anonymous donations', 'kudos-donations')}
					help={__(
						'Allow users to donate without leaving a name or email address. Anonymous users can only perform one-off donations.',
						'kudos-donations'
					)}
				/>
				<Divider />
				<ToggleControl
					name="meta.address_enabled"
					label={__('Address', 'kudos-donations')}
					help={__('Show the address tab.', 'kudos-donations')}
				/>
				<CheckboxControl
					name="meta.address_required"
					isDisabled={!watchAddress}
					help={__('Make the address required.', 'kudos-donations')}
					label={' '}
					altLabel={__('Required', 'kudos-donations')}
				/>
				<Divider />
				<ToggleControl
					name="meta.message_enabled"
					label={__('Message', 'kudos-donations')}
					help={__(
						'Allow donors to leave a message.',
						'kudos-donations'
					)}
				/>
			</Panel>
			<Panel title={__('Policy links', 'kudos-donations')}>
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
		</Fragment>
	);
};
