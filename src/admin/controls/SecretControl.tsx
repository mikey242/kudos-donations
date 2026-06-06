import React from 'react';
import { useState } from '@wordpress/element';
import {
	Button,
	Icon,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControl as InputControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalInputControlPrefixWrapper as InputControlPrefixWrapper,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSettingsContext } from '../contexts';

interface SecretControlProps {
	name: string;
	label: string;
	help?: string;
	validate?: (value: string) => true | string;
	resetWith?: Record<string, unknown>;
}

export const SecretControl = ({
	name,
	label,
	help,
	validate,
	resetWith,
}: SecretControlProps) => {
	const { settings, updateSetting, updateSettings } =
		useSettingsContext<any>();
	const savedValue = (settings[name] as string) ?? '';
	const isSet = !!savedValue;

	const [inputValue, setInputValue] = useState('');
	const [isSaving, setIsSaving] = useState(false);
	const [confirming, setConfirming] = useState(false);
	const [error, setError] = useState<string | null>(null);

	const handleApply = async () => {
		if (!inputValue) {
			return;
		}
		if (validate) {
			const result = validate(inputValue);
			if (result !== true) {
				setError(result);
				return;
			}
		}
		setError(null);
		setIsSaving(true);
		try {
			await updateSetting(name, inputValue);
			setInputValue('');
		} finally {
			setIsSaving(false);
		}
	};

	const handleReset = async () => {
		setIsSaving(true);
		try {
			await updateSettings({ [name]: '', ...(resetWith ?? {}) });
		} finally {
			setIsSaving(false);
			setConfirming(false);
		}
	};

	const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
		if (e.key === 'Enter') {
			e.preventDefault();
			void handleApply();
		}
	};

	return (
		<InputControl
			label={label}
			type="text"
			value={
				isSet
					? `${label} ${__('saved', 'kudos-donations')}`.toUpperCase()
					: inputValue
			}
			disabled={isSet || isSaving}
			onChange={(value) => {
				setInputValue(value ?? '');
				if (error) {
					setError(null);
				}
			}}
			onKeyDown={handleKeyDown}
			help={error ?? help}
			prefix={
				<InputControlPrefixWrapper>
					<Icon icon="shield" />
				</InputControlPrefixWrapper>
			}
			suffix={
				<>
					{!isSet && (
						<Button
							variant="secondary"
							onClick={() => void handleApply()}
							isBusy={isSaving}
							disabled={isSaving || !inputValue}
							__next40pxDefaultSize
						>
							{__('Apply', 'kudos-donations')}
						</Button>
					)}
					{isSet && !confirming && (
						<Button
							variant="secondary"
							isDestructive
							onClick={() => setConfirming(true)}
							disabled={isSaving}
							__next40pxDefaultSize
						>
							{__('Reset', 'kudos-donations')}
						</Button>
					)}
					{isSet && confirming && (
						<>
							<Button
								variant="primary"
								isDestructive
								onClick={() => void handleReset()}
								isBusy={isSaving}
								disabled={isSaving}
								__next40pxDefaultSize
							>
								{__('Confirm reset', 'kudos-donations')}
							</Button>
							<Button
								variant="secondary"
								onClick={() => setConfirming(false)}
								disabled={isSaving}
								__next40pxDefaultSize
							>
								{__('Cancel', 'kudos-donations')}
							</Button>
						</>
					)}
				</>
			}
			__next40pxDefaultSize
		/>
	);
};
