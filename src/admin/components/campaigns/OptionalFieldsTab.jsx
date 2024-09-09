import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { CheckboxControl } from '../../../front/components/controls';
import { TextControl, ToggleControl } from '../controls';
import React from 'react';
import { useWatch } from 'react-hook-form';
import { Panel, PanelBody } from '@wordpress/components';

export const OptionalFieldsTab = () => {
	const watchAddress = useWatch({ name: 'meta.address_enabled' });

	return (
		<Fragment>
			<Panel header={__('Optional fields', 'kudos-donations')}>
				<PanelBody>
					<ToggleControl
						name="meta.allow_anonymous"
						label={__(
							'Allow anonymous donations',
							'kudos-donations'
						)}
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
						help={__(
							'Make the address required.',
							'kudos-donations'
						)}
						label={' '}
						altLabel={__('Required', 'kudos-donations')}
					/>
					<ToggleControl
						name="meta.message_enabled"
						label={__('Message', 'kudos-donations')}
						help={__(
							'Allow donors to leave a message.',
							'kudos-donations'
						)}
					/>
				</PanelBody>
			</Panel>
			<Panel header={__('Policy links', 'kudos-donations')}>
				<PanelBody>
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
				</PanelBody>
			</Panel>
		</Fragment>
	);
};
