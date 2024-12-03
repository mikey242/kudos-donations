import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import { CampaignsPage } from './components/campaigns/CampaignsPage';
import { AdminProvider, CampaignsProvider } from './components';

domReady(() => {
	const container = document.getElementById('kudos-campaigns');
	if (container) {
		const root = createRoot(container);
		root.render(
			<AdminProvider>
				<CampaignsProvider>
					<CampaignsPage />
				</CampaignsProvider>
			</AdminProvider>
		);
	}
});
