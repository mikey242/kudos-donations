import { createRoot } from '@wordpress/element';
import React from 'react';
import SettingsPage from '../components/admin/settings/SettingsPage';
import SettingsProvider from '../components/common/contexts/SettingsContext';
import { NotificationProvider } from '../components/common/contexts/NotificationContext';
import Render from '../components/common/Render';
import './kudos-admin-settings.css';
import { BrowserRouter } from 'react-router-dom';
import { QueryParamProvider } from 'use-query-params';
import { ReactRouter6Adapter } from 'use-query-params/adapters/react-router-6';
import domReady from '@wordpress/dom-ready';

const container = document.getElementById('kudos-settings');
const root = createRoot(container);

domReady(() => {
	root.render(
		<Render>
			<NotificationProvider>
				<SettingsProvider>
					<BrowserRouter>
						<QueryParamProvider adapter={ReactRouter6Adapter}>
							<SettingsPage />
						</QueryParamProvider>
					</BrowserRouter>
				</SettingsProvider>
			</NotificationProvider>
		</Render>
	);
});
