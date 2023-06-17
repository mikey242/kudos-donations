import { createRoot } from '@wordpress/element';
import React from 'react';
import { CampaignsPage } from '../../components/admin/CampaignsPage';
import SettingsProvider from '../../contexts/SettingsContext';
import Render from '../../components/Render';
import { NotificationProvider } from '../../contexts/NotificationContext';
import '../kudos-admin.css'

const container = document.getElementById('kudos-settings');
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
