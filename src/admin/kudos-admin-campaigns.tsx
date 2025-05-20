import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import { CampaignsPage } from './components/campaigns/CampaignsPage';
import { AdminProvider, PostsProvider } from './components';
import type { Campaign } from '../types/posts';
import { __ } from '@wordpress/i18n';

domReady(() => {
	const container = document.getElementById('root');
	if (container) {
		const root = createRoot(container);
		root.render(
			<AdminProvider>
				<PostsProvider<Campaign>
					postType="kudos_campaign"
					singular={__('Campaign', 'kudos-donations')}
				>
					<CampaignsPage />
				</PostsProvider>
			</AdminProvider>
		);
	}
});
