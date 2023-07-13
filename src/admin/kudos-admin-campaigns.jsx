import { createRoot } from '@wordpress/element';
import React from 'react';
import { CampaignsPage } from '../components/campaigns/CampaignsPage';
import SettingsProvider from '../contexts/SettingsContext';
import Render from '../components/Render';
import { NotificationProvider } from '../contexts/NotificationContext';
import './kudos-admin-campaigns.css'

const container = document.getElementById('kudos-campaigns');
const root = createRoot(container);
root.render(
	<Render>
		<NotificationProvider>
			<SettingsProvider>
				<CampaignsPage />
			</SettingsProvider>
		</NotificationProvider>
	</Render>
);
