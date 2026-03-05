import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import './kudos-admin.css';
import { AdminProvider, SettingsProvider } from './contexts';
import { AdminRouter, Panel } from './components';
import * as Controls from './components/controls';

window.kudos.admin = {
	Controls,
	Components: {
		Panel,
	},
};

domReady(() => {
	const container = document.getElementById('root');
	if (container) {
		const root = createRoot(container);
		root.render(
			<AdminProvider>
				<SettingsProvider>
					<AdminRouter />
				</SettingsProvider>
			</AdminProvider>
		);
	}
});
