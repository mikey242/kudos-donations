import { createRoot } from '@wordpress/element';
import React from 'react';
import { CampaignsPage } from '../components/admin/campaigns/CampaignsPage';
import SettingsProvider from '../components/common/contexts/SettingsContext';
import Render from '../components/common/Render';
import { NotificationProvider } from '../components/common/contexts/NotificationContext';
import './kudos-admin-campaigns.css';
import { BrowserRouter } from 'react-router-dom';
import { QueryParamProvider } from 'use-query-params';
import { ReactRouter6Adapter } from 'use-query-params/adapters/react-router-6';
import AdminTableProvider from '../components/common/contexts/AdminTableContext';
import domReady from '@wordpress/dom-ready';

const container = document.getElementById('kudos-campaigns');
const root = createRoot(container);

domReady(() => {
	root.render(
		<Render>
			<NotificationProvider>
				<SettingsProvider>
					<BrowserRouter>
						<QueryParamProvider adapter={ReactRouter6Adapter}>
							<AdminTableProvider
								postType="kudos_campaign"
								singular="campaign"
								plural="campaigns"
							>
								<CampaignsPage />
							</AdminTableProvider>
						</QueryParamProvider>
					</BrowserRouter>
				</SettingsProvider>
			</NotificationProvider>
		</Render>
	);
});
