import { render } from '@wordpress/element';
import React from 'react';
import { KudosCampaigns } from './components/campaigns/KudosCampaigns';
import SettingsProvider from '../common/contexts/SettingsContext';
import Render from '../common/components/Render';
import { NotificationProvider } from '../common/contexts/NotificationContext';

const container = document.getElementById('kudos-settings');
render(
	<Render>
		<NotificationProvider>
			<SettingsProvider>
				<KudosCampaigns />
			</SettingsProvider>
		</NotificationProvider>
	</Render>,
	container
);
