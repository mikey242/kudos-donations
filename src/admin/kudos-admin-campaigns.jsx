import { render } from '@wordpress/element';
import React from 'react';
import { CampaignsPage } from './components/CampaignsPage';
import SettingsProvider from './contexts/SettingsContext';
import Render from '../common/components/Render';
import { NotificationProvider } from './contexts/NotificationContext';

const container = document.getElementById('kudos-settings');
render(
	<Render>
		<NotificationProvider>
			<SettingsProvider>
				<CampaignsPage />
			</SettingsProvider>
		</NotificationProvider>
	</Render>,
	container
);
