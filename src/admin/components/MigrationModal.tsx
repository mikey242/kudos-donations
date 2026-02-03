import React, { useState } from 'react';
import {
	Button,
	CheckboxControl,
	Flex,
	Modal,
	Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

interface MigrationProgress {
	version: string;
	job: string;
	complete: boolean;
	offset: number;
}

interface MigrationResponse {
	success: boolean;
	done: boolean;
	progress?: MigrationProgress;
}

export const MigrationModal = () => {
	const [hasBackedUp, setHasBackedUp] = useState(false);
	const [isRunning, setIsRunning] = useState(false);
	const [status, setStatus] = useState<string | null>(null);
	const [error, setError] = useState<string | null>(null);
	const [isDone, setIsDone] = useState(false);

	const runMigration = async () => {
		setIsRunning(true);
		setError(null);
		setStatus(__('Starting migration…', 'kudos-donations'));

		let done = false;

		while (!done) {
			try {
				const result: MigrationResponse = await apiFetch({
					path: '/kudos/v1/migration/run/',
					method: 'POST',
				});

				if (!result.success) {
					setError(__('Migration failed', 'kudos-donations'));
					setIsRunning(false);
					return;
				}

				if (result.progress) {
					setStatus(
						`${result.progress.version}: ${result.progress.job}`
					);
				}

				done = result.done === true;
			} catch (err) {
				setError(
					err instanceof Error
						? err.message
						: __('Migration failed', 'kudos-donations')
				);
				setIsRunning(false);
				return;
			}
		}

		setStatus(__('Migration complete.', 'kudos-donations'));
		setIsRunning(false);
		setIsDone(true);
	};

	const handleReload = () => {
		window.location.reload();
	};

	const handleCancel = () => {
		window.history.back();
	};

	return (
		<Modal
			isDismissible={false}
			shouldCloseOnClickOutside={false}
			onRequestClose={handleCancel}
			title={__('Upgrade required', 'kudos-donations')}
		>
			<p>
				{__(
					'Kudos Donations needs to update your database before you can continue.',
					'kudos-donations'
				)}
			</p>
			<p>
				<strong>
					{__(
						'Please make sure you backup your data before proceeding.',
						'kudos-donations'
					)}
				</strong>
			</p>

			{!isRunning && !isDone && (
				<>
					<CheckboxControl
						label={__(
							'I have backed up my data',
							'kudos-donations'
						)}
						checked={hasBackedUp}
						onChange={setHasBackedUp}
					/>
					<br />
					<Flex>
						<Button
							onClick={runMigration}
							variant="primary"
							disabled={!hasBackedUp}
						>
							{__('Update now', 'kudos-donations')}
						</Button>
						<Button variant="secondary" onClick={handleCancel}>
							{__('Cancel', 'kudos-donations')}
						</Button>
					</Flex>
				</>
			)}

			{isRunning && (
				<Flex justify={'flex-start'}>
					<Spinner style={{ margin: '0' }} /> {status}
				</Flex>
			)}

			{error && (
				<p style={{ color: '#d63638' }}>
					{__('Error:', 'kudos-donations')} {error}
				</p>
			)}

			{isDone && (
				<>
					<p style={{ color: '#00a32a' }}>{status}</p>
					<Button onClick={handleReload} variant="primary">
						{__('Reload page', 'kudos-donations')}
					</Button>
				</>
			)}
		</Modal>
	);
};
