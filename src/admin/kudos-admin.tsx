import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import './kudos-admin.css';
import { AdminProvider, SettingsProvider } from './components';
import { AdminRouter } from './components/AdminRouter';

domReady(() => {
	const container = document.getElementById('root');
	const defaultView = container?.dataset?.view ?? 'campaigns';
	if (container) {
		const root = createRoot(container);
		root.render(
			<AdminProvider>
				<SettingsProvider>
					<div className="admin-wrap">
						<AdminRouter defaultView={defaultView} />
					</div>
				</SettingsProvider>
			</AdminProvider>
		);
	}
});
