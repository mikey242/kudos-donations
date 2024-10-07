import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import { SettingsPage } from './components/settings/SettingsPage';
import './kudos-admin.css';
import { AdminProvider } from './contexts/AdminContext';
import { BrowserRouter } from 'react-router-dom';

const container = document.getElementById('kudos-settings');
const root = createRoot(container);

domReady(() => {
	root.render(
		<BrowserRouter>
			<AdminProvider>
				<SettingsPage />
			</AdminProvider>
		</BrowserRouter>
	);
});
