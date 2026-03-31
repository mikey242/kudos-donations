import { __ } from '@wordpress/i18n';
import React from 'react';
import { useState } from '@wordpress/element';
import { useSettingsContext } from '../../../contexts';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import apiFetch from '@wordpress/api-fetch';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControl as InputControl,
	Button,
	ExternalLink,
	Flex,
	Icon,
} from '@wordpress/components';
import { Panel } from '../../../components';
import type { AllSettings } from '../../../../types/all-settings';

type InstallState = 'idle' | 'installing' | 'installed' | 'failed';

const PlusTab = (): React.ReactNode => {
	const { updateSetting, settings } = useSettingsContext<AllSettings>();
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);
	const [pendingKey, setPendingKey] = useState('');
	const [activating, setActivating] = useState(false);
	const [installState, setInstallState] = useState<InstallState>(
		window.kudos?.isAddonInstalled ? 'installed' : 'idle'
	);

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

	const handleInstall = async () => {
		setInstallState('installing');
		try {
			await apiFetch({
				path: '/kudos/v1/licence/install-addon',
				method: 'POST',
			});
			setInstallState('installed');
			void createSuccessNotice(
				__('Add-on installed and activated.', 'kudos-donations'),
				{ type: 'snackbar' }
			);
		} catch {
			setInstallState('failed');
			void createErrorNotice(
				__(
					'Failed to install add-on. Please check the logs and try again.',
					'kudos-donations'
				)
			);
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
			{licenceStatus?.valid && (
				<Panel header={__('Add-on', 'kudos-donations')}>
					<Flex justify="space-between" align="center">
						<span>
							{__('Kudos Donations Plus', 'kudos-donations')}
						</span>
						{installState === 'installed' ? (
							<Button
								type="button"
								disabled={true}
								icon={<Icon icon="yes-alt" />}
							>
								{__('Add-on installed', 'kudos-donations')}
							</Button>
						) : (
							<Button
								type="button"
								variant="primary"
								isBusy={installState === 'installing'}
								disabled={installState === 'installing'}
								onClick={() => void handleInstall()}
							>
								{installState === 'failed'
									? __('Retry install', 'kudos-donations')
									: __('Install add-on', 'kudos-donations')}
							</Button>
						)}
					</Flex>
				</Panel>
			)}
			<Panel header={__('Licence key', 'kudos-donations')}>
				<p>
					{__(
						'Add additional functionality to Kudos Donations and help fund future development. Click the "Visit documentation" link below for more information.',
						'kudos-donations'
					)}
				</p>
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
					<ExternalLink href="https://docs.kudosdonations.com/docs/plus">
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

export { PlusTab };
