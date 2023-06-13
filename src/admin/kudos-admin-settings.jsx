import { render } from '@wordpress/element';
import React from 'react';
import SettingsPage from './components/SettingsPage';
import '../../assets/images/logo-colour-40.png';
import SettingsProvider from './contexts/SettingsContext';
import { NotificationProvider } from './contexts/NotificationContext';
import Render from '../common/components/Render';

const root = document.getElementById('kudos-settings');
render(
	<Render>
		<NotificationProvider>
			<SettingsProvider>
				<SettingsPage />
			</SettingsProvider>
		</NotificationProvider>
	</Render>,
	root
);
