import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import { CampaignsPage } from './components/campaigns/CampaignsPage';
import CampaignsProvider from './contexts/CampaignsContext';
import './kudos-admin-campaigns.css';
import { AdminProvider } from './contexts/AdminContext';
import { BrowserRouter } from 'react-router-dom';

const container = document.getElementById('kudos-campaigns');
const root = createRoot(container);

domReady(() => {
	root.render(
		<BrowserRouter>
			<CampaignsProvider>
				<AdminProvider>
					<CampaignsPage />
				</AdminProvider>
			</CampaignsProvider>
		</BrowserRouter>
	);
});
