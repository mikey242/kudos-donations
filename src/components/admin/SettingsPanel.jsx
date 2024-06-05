import React from 'react';
import { forwardRef } from '@wordpress/element';
import Panel from '../Panel';
import { clsx } from 'clsx';

const SettingsPanel = forwardRef(({ children, title, className = '' }) => {
	return (
		<Panel className={clsx('p-6 space-y-6', className)} title={title}>
			{children}
		</Panel>
	);
});

export default SettingsPanel;
