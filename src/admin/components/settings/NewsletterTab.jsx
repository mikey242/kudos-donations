import { __ } from '@wordpress/i18n';
import { Button, Panel, PanelBody } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { store as noticesStore } from '@wordpress/notices';
import { useDispatch } from '@wordpress/data';
import { SelectControl, TextControl } from '../controls';
import { useSettingsContext } from '../../contexts/SettingsContext';
import { useWatch } from 'react-hook-form';
import { useState } from '@wordpress/element';
import React from 'react';
import { MailchimpProvider } from './providers/MailchimpProvider';
import { MailerliteProvider } from './providers/MailerliteProvider';

export const NewsletterTab = () => {
	const { settings } = useSettingsContext();
	const provider = useWatch({ name: '_kudos_newsletter_provider' });
	const {
		kudos: { newsletter_providers: providers },
	} = window;
	const [isBusy, setIsBusy] = useState(false);
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);

	const refresh = () => {
		setIsBusy(true);
		apiFetch({
			path: '/kudos/v1/newsletter/refresh/',
			method: 'GET',
		})
			.then((response) => {
				void createSuccessNotice(response?.message, {
					type: 'snackbar',
				});
			})
			.catch((error) => {
				void createErrorNotice(error?.message, {
					type: 'snackbar',
				});
			})
			.finally(() => {
				setIsBusy(false);
			});
	};

	const MailerProvider = (props) => {
		switch (provider) {
			case 'mailchimp':
				return <MailchimpProvider {...props} />;
			case 'mailerlite':
				return <MailerliteProvider {...props} />;
			default:
				return null;
		}
	};

	return (
		<>
			<Panel header={__('Newsletter Provider', 'kudos-donations')}>
				<PanelBody>
					<SelectControl
						name="_kudos_newsletter_provider"
						label={__('Newsletter provider', 'kudos-donations')}
						help={__(
							'Select your preferred newsletter provider.',
							'kudos-donations'
						)}
						value={''}
						options={
							providers.length &&
							providers?.map((item) => {
								return {
									label: item.label,
									value: item.slug,
								};
							})
						}
					/>
					<TextControl
						name="_kudos_newsletter_checkbox_text"
						label={__('Checkbox text', 'kudos-donations')}
						help={__(
							'Choose the text that will be displayed on the checkbox to agree to your newsletter',
							'kudos-donations'
						)}
					/>
				</PanelBody>
			</Panel>
			{provider === settings._kudos_newsletter_provider ? (
				<MailerProvider refresh={refresh} isBusy={isBusy} />
			) : (
				<Button variant="primary" type="submit">
					{__('Apply change', 'kudos-donations')}
				</Button>
			)}
		</>
	);
};
