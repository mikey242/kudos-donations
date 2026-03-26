import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import './kudos-admin.css';
import { AdminProvider, SettingsProvider } from './contexts';
import { AdminRouter } from './pages';
import * as Controls from './controls';
import * as Components from './components';
import * as Contexts from './contexts';
import { getLicenceStatus } from '../licence-utils';

window.kudos.getLicenceStatus = getLicenceStatus;

window.kudos.admin = { Controls, Components, Contexts };

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
