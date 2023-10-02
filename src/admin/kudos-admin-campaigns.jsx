import { createRoot } from '@wordpress/element';
import React from 'react';
import { CampaignsPage } from '../components/campaigns/CampaignsPage';
import SettingsProvider from '../contexts/SettingsContext';
import Render from '../components/Render';
import { NotificationProvider } from '../contexts/NotificationContext';
import './kudos-admin-campaigns.css';
import { BrowserRouter } from 'react-router-dom';
import { QueryParamProvider } from 'use-query-params';
import { ReactRouter6Adapter } from 'use-query-params/adapters/react-router-6';

const container = document.getElementById('kudos-campaigns');
const root = createRoot(container);

root.render(
	<Render>
		<NotificationProvider>
			<SettingsProvider>
				<BrowserRouter>
					<QueryParamProvider adapter={ReactRouter6Adapter}>
						<CampaignsPage />
					</QueryParamProvider>
				</BrowserRouter>
			</SettingsProvider>
		</NotificationProvider>
	</Render>
);
