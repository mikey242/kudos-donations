import { __ } from '@wordpress/i18n';
import React from 'react';
import { useState } from '@wordpress/element';
import { useSettingsContext } from '../../../contexts';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControl as InputControl,
	Button,
	ExternalLink,
	Flex,
	Icon,
} from '@wordpress/components';
import { Panel } from '../../Panel';
import type { AllSettings } from '../../../../types/all-settings';

const LicenceTab = (): React.ReactNode => {
	const { updateSetting, settings } = useSettingsContext<AllSettings>();
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);
	const [pendingKey, setPendingKey] = useState('');
	const [activating, setActivating] = useState(false);

	const {
		_kudos_licence_key: licenceKey,
		_kudos_licence_status: licenceStatus,
	} = settings;

	const isSet = !!licenceKey;

	const handleActivate = async () => {
		setActivating(true);
		try {
			const response = await updateSetting(
				'_kudos_licence_key',
				pendingKey as AllSettings['_kudos_licence_key']
			);
			const newStatus = (response as AllSettings)._kudos_licence_status;
			if (newStatus?.valid) {
				setPendingKey('');
				void createSuccessNotice(
					__('Licence activated', 'kudos-donations'),
					{ type: 'snackbar' }
				);
			} else {
				void createErrorNotice(
					__(
						'Invalid licence key. Please check and try again.',
						'kudos-donations'
					)
				);
			}
		} finally {
			setActivating(false);
		}
	};

	const handleReset = async () => {
		await updateSetting(
			'_kudos_licence_key',
			'' as AllSettings['_kudos_licence_key']
		);
		void createSuccessNotice(__('Licence reset', 'kudos-donations'), {
			type: 'snackbar',
		});
	};

	return (
		<>
			<Panel header={__('Licence key', 'kudos-donations')}>
				<InputControl
					__next40pxDefaultSize
					label={__('Licence key', 'kudos-donations')}
					value={isSet ? licenceKey : pendingKey}
					type={isSet ? 'password' : 'text'}
					readOnly={isSet}
					disabled={isSet || activating}
					onChange={isSet ? () => {} : setPendingKey}
				/>
				{licenceStatus?.valid && (
					<Flex
						justify="flex-end"
						gap={1}
						style={{ marginBottom: '1em' }}
					>
						<Icon icon="yes-alt" />
						<span>
							{__('Valid until:', 'kudos-donations') +
								' ' +
								licenceStatus.expires_at}
						</span>
					</Flex>
				)}
				<Flex justify="space-between">
					<ExternalLink href="https://docs.kudosdonations.com">
						{__('Visit documentation', 'kudos-donations')}
					</ExternalLink>
					{isSet ? (
						<Button
							type="button"
							variant="link"
							isDestructive={true}
							onClick={() => void handleReset()}
						>
							{__('Reset licence', 'kudos-donations')}
						</Button>
					) : (
						<Button
							type="button"
							variant="primary"
							isBusy={activating}
							disabled={activating || !pendingKey}
							onClick={() => void handleActivate()}
						>
							{__('Activate', 'kudos-donations')}
						</Button>
					)}
				</Flex>
			</Panel>
		</>
	);
};

export { LicenceTab };
