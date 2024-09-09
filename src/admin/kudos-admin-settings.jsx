import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import { SettingsPage } from './components/settings/SettingsPage';
import SettingsProvider from './contexts/SettingsContext';
import './kudos-admin.css';
import { AdminProvider } from './contexts/AdminContext';

const container = document.getElementById('kudos-settings');
const root = createRoot(container);

domReady(() => {
	root.render(
		<SettingsProvider>
			<AdminProvider>
				<SettingsPage />
			</AdminProvider>
		</SettingsProvider>
	);
});
