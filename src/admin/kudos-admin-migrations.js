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
				'Running migrations in the background. This might take a minute.',
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
				if (response.completed) {
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
				} else {
					migrationStatus.textContent = sprintf(
						/* translators: %s is number of migrations processed */
						__(`Processed %s migrations…`),
						response.next_offset
					);
					processMigrations(response.next_offset, batch); // Process the next batch.
				}
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
