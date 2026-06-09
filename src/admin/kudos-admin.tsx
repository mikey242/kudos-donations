import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import './kudos-admin.css';
import { AdminProvider } from './contexts';
import { AdminRouter } from './pages';
import * as Controls from './controls';
import * as Components from './components';
import * as Contexts from './contexts';
import * as Utils from './utils';

window.kudos.admin.api = {
	Controls,
	Components,
	Contexts,
	Utils,
};

domReady(() => {
	const container = document.getElementById('root');
	if (container) {
		const root = createRoot(container);
		root.render(
			<AdminProvider>
				<AdminRouter />
			</AdminProvider>
		);
	}
});
