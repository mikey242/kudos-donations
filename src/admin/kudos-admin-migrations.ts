import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

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

document.addEventListener('DOMContentLoaded', () => {
	const migrateButton = document.getElementById(
		'kudos-migrate-button'
	) as HTMLButtonElement | null;
	const migrationStatus = document.getElementById(
		'kudos-migration-status'
	) as HTMLElement | null;
	if (migrateButton) {
		migrateButton.addEventListener('click', (e) => {
			e.preventDefault();
			migrateButton.style.display = 'none';
			migrateButton.disabled = true;
			migrationStatus.textContent = __(
				'Starting migration. This might take a minute.',
				'kudos-donations'
			);

			void processMigrations();
		});
	}

	async function processMigrations() {
		let done = false;

		while (!done) {
			try {
				const result: MigrationResponse = await apiFetch({
					path: '/kudos/v1/migration/run/',
					method: 'POST',
				});

				migrationStatus.textContent = result.progress?.job;

				if (!result.success) {
					throw new Error('Migration failed');
				}

				done = result.done === true;
			} catch (error) {
				migrationStatus.textContent = 'Migration failed.';
				return;
			}
		}

		migrationStatus.textContent = __(
			'Migration complete.',
			'kudos-donations'
		);
	}
});
