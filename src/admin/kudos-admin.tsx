import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import './kudos-admin.css';
import { AdminProvider, SettingsProvider } from './contexts';
import { AdminRouter } from './components';

domReady(() => {
	const container = document.getElementById('root');
	if (container) {
		const root = createRoot(container);
		root.render(
			<AdminProvider>
				<SettingsProvider>
					<div className="admin-wrap">
						<AdminRouter />
					</div>
				</SettingsProvider>
			</AdminProvider>
		);
	}
});
