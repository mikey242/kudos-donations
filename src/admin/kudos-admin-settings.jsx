import { createRoot } from '@wordpress/element';
import React from 'react';
import SettingsPage from '../components/settings/SettingsPage';
import SettingsProvider from '../contexts/SettingsContext';
import { NotificationProvider } from '../contexts/NotificationContext';
import Render from '../components/Render';
import './kudos-admin-settings.css';

const container = document.getElementById('kudos-settings');
const root = createRoot(container);

root.render(
	<Render>
		<NotificationProvider>
			<SettingsProvider>
				<SettingsPage />
			</SettingsProvider>
		</NotificationProvider>
	</Render>
);
