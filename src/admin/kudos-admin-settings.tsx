import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import { SettingsPage } from './components/settings/SettingsPage';
import './kudos-admin.css';
import { AdminProvider, SettingsProvider } from './components';

domReady(() => {
	const container = document.getElementById('root');
	if (container) {
		const root = createRoot(container);
		root.render(
			<AdminProvider>
				<SettingsProvider>
					<SettingsPage />
				</SettingsProvider>
			</AdminProvider>
		);
	}
});
