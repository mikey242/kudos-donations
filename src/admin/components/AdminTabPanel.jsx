import React from 'react';
import { TabPanel } from '@wordpress/components';

export const AdminTabPanel = ({ tabs }) => {
	return (
		<div className="kudos-settings-tab-panel">
			<TabPanel
				initialTabName={getActiveTabFromQuery()}
				onSelect={updateQueryVar}
				tabs={tabs}
			>
				{(tab) => tab.content}
			</TabPanel>
		</div>
	);
};

const getActiveTabFromQuery = () => {
	const params = new URLSearchParams(window.location.search);
	return params.get('tab');
};

const updateQueryVar = (tabName) => {
	const params = new URLSearchParams(window.location.search);
	params.set('tab', tabName);
	const newUrl = `${window.location.pathname}?${params.toString()}`;
	window.history.replaceState(null, '', newUrl);
};
