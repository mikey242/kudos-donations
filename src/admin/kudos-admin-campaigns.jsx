import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import { CampaignsPage } from './components/campaigns/CampaignsPage';
import CampaignsProvider from './contexts/CampaignsContext';
import { AdminProvider } from './contexts/AdminContext';
import { BrowserRouter } from 'react-router-dom';

domReady(() => {
	const container = document.getElementById('kudos-campaigns');
	if (container) {
		const root = createRoot(container);
		root.render(
			<BrowserRouter>
				<AdminProvider>
					<CampaignsProvider>
						<CampaignsPage />
					</CampaignsProvider>
				</AdminProvider>
			</BrowserRouter>
		);
	}
});
