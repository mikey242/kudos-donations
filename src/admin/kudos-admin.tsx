import { createRoot } from '@wordpress/element';
import React from 'react';
import domReady from '@wordpress/dom-ready';
import './kudos-admin.css';
import {
	AdminProvider,
	SettingsProvider,
	useSettingsContext,
	useAdminContext,
	useEntitiesContext,
} from './contexts';
import { AdminRouter, Panel } from './components';
import {
	SLOT_HEADER_ACTIONS,
	SLOT_HEADER_ACTIONS_EXTRA,
	SLOT_PAGE_TITLE,
} from './slot-names';
import * as Controls from './components/controls';
import { useFormContext } from 'react-hook-form';

window.kudos.admin = {
	Controls,
	Components: {
		Panel,
	},
	Hooks: {
		useAdminContext,
		useSettingsContext,
		useEntitiesContext,
		useFormContext,
	},
	SlotNames: {
		SLOT_HEADER_ACTIONS,
		SLOT_HEADER_ACTIONS_EXTRA,
		SLOT_PAGE_TITLE,
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
