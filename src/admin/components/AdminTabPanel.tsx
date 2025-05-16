import React from 'react';
import { TabPanel } from '@wordpress/components';
import { useAdminContext } from './contexts';

export interface AdminTab {
	name: string;
	title: string;
	content: React.ReactNode;
}

interface AdminTabPanelProps {
	tabs: AdminTab[];
}

export const AdminTabPanel = ({
	tabs,
}: AdminTabPanelProps): React.ReactNode => {
	const { searchParams, updateParam } = useAdminContext();

	const updateTab = (tabName: string) => {
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
