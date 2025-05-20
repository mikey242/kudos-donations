import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import { AdminProvider, PostsProvider } from './components';
import type { Campaign } from '../types/posts';
import { __ } from '@wordpress/i18n';
import { TransactionsTable } from './components/transactions/TransactionsTable';

domReady(() => {
	const container = document.getElementById('root');
	if (container) {
		const root = createRoot(container);
		root.render(
			<AdminProvider>
				<PostsProvider<Campaign>
					postType="kudos_transaction"
					singular={__('Transaction', 'kudos-donations')}
				>
					<TransactionsTable />
				</PostsProvider>
			</AdminProvider>
		);
	}
});
