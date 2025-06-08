import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import './kudos-admin.css';
import { AdminProvider, SettingsProvider } from './contexts';
import { AdminRouter } from './components';

domReady(() => {
	const container = document.getElementById('root');
	const defaultView = container.dataset.view;
	if (container) {
		const root = createRoot(container);
		root.render(
			<AdminProvider>
				<SettingsProvider>
					<AdminRouter defaultView={defaultView} />
				</SettingsProvider>
			</AdminProvider>
		);
	}
});
