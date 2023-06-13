import { createRoot } from '@wordpress/element';
import React from 'react';
import SettingsPage from './components/SettingsPage';
import '../../assets/images/logo-colour-40.png';
import SettingsProvider from './contexts/SettingsContext';
import { NotificationProvider } from './contexts/NotificationContext';
import Render from '../common/components/Render';

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
