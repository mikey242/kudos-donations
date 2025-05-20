import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import { AdminProvider, PostsProvider } from './components';
import type { Subscription } from '../types/posts';
import { __ } from '@wordpress/i18n';
import { SubscriptionsTable } from './components/subscriptions/SubscriptionsTable';

domReady(() => {
	const container = document.getElementById('root');
	if (container) {
		const root = createRoot(container);
		root.render(
			<AdminProvider>
				<PostsProvider<Subscription>
					postType="kudos_subscription"
					singular={__('Subscription', 'kudos-donations')}
				>
					<SubscriptionsTable />
				</PostsProvider>
			</AdminProvider>
		);
	}
});
