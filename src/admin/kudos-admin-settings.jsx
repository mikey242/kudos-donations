import { render } from '@wordpress/element';
import React from 'react';
import KudosSettings from './components/settings/KudosSettings';
import '../images/logo-colour-40.png';
import SettingsProvider from '../common/contexts/SettingsContext';
import { NotificationProvider } from '../common/contexts/NotificationContext';
import Render from '../common/components/Render';

const root = document.getElementById('kudos-settings');
render(
	<Render>
		<NotificationProvider>
			<SettingsProvider>
				<KudosSettings />
			</SettingsProvider>
		</NotificationProvider>
	</Render>,
	root
);
