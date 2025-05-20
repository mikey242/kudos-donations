import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import { AdminProvider, PostsProvider } from './components';
import type { Campaign } from '../types/posts';
import { __ } from '@wordpress/i18n';
import { DonorsTable } from './components/donors/DonorsTable';

domReady(() => {
	const container = document.getElementById('root');
	if (container) {
		const root = createRoot(container);
		root.render(
			<AdminProvider>
				<PostsProvider<Campaign>
					postType="kudos_donor"
					singular={__('Donor', 'kudos-donations')}
				>
					<DonorsTable />
				</PostsProvider>
			</AdminProvider>
		);
	}
});
