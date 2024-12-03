import React from 'react';
import { TabPanel } from '@wordpress/components';
import { useAdminContext } from './contexts';

export const AdminTabPanel = ({ tabs }) => {
	const { searchParams, updateParam } = useAdminContext();

	const updateTab = (tabName) => {
		updateParam('tab', tabName);
	};

	return (
		<div className="kudos-settings-tab-panel">
			<TabPanel
				initialTabName={searchParams.get('tab')}
				onSelect={updateTab}
				tabs={tabs}
			>
				{(tab) => tab.content}
			</TabPanel>
		</div>
	);
};
