import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';

document.addEventListener('DOMContentLoaded', () => {
	const migrateButton = document.getElementById('kudos-migrate-button');
	const migrationStatus = document.getElementById('kudos-migration-status');
	const batchSize = 1;
	const offset = 0;

	if (migrateButton) {
		migrateButton.addEventListener('click', (e) => {
			e.preventDefault();
			migrateButton.style.display = 'none';
			migrateButton.disabled = true;
			migrationStatus.textContent = __(
				'Starting migration. This might take a minute.',
				'kudos-donations'
			);

			void processMigrations(offset, batchSize);
		});
	}

	function processMigrations(currentOffset, batch) {
		apiFetch({
			path: '/kudos/v1/migration/migrate/',
			method: 'POST',
			data: {
				offset: currentOffset,
				batch_size: batch,
			},
		})
			.then((response) => {
				// eslint-disable-next-line camelcase
				const { completed, next_offset, progress } = response;
				if (completed) {
					// Dismiss notice that has been created.
					void apiFetch({
						path: '/kudos/v1/notice/dismiss',
						method: 'POST',
						data: {
							id: 'kudos-migration-complete',
						},
					});
					migrationStatus.textContent =
						'Migrations completed successfully!';
					return;
				}

				const currentStep = progress?.running;
				const stepOffset = progress?.steps?.[currentStep]?.offset ?? 0;

				const stepLabel = currentStep
					? currentStep
							.replace(/_/g, ' ')
							.replace(/\b\w/g, (l) => l.toUpperCase())
					: __('Working…');

				migrationStatus.textContent = sprintf(
					// translators: %1$s is the step label (e.g Transactions) and %2$s is the step offset (e.g. 1000)
					__('Migrating %1$s: %2$s records processed'),
					stepLabel,
					stepOffset
				);

				// eslint-disable-next-line camelcase
				processMigrations(next_offset ?? 0, batch);
			})
			.catch((error) => {
				if (error?.message) {
					migrationStatus.textContent = error.message;
				} else {
					migrationStatus.textContent =
						'Migration failed. Please check the logs.';
				}
			});
	}
});
