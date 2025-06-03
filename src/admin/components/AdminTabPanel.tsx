import React from 'react';
import {
	TabPanel,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { useQueryState } from 'nuqs';

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
	const [tabName, setTabName] = useQueryState('tab');

	const updateTab = async (tab: string) => {
		await setTabName(tab);
	};

	return (
		<div className="kudos-settings-tab-panel">
			<TabPanel initialTabName={tabName} onSelect={updateTab} tabs={tabs}>
				{(tab) => <VStack spacing={4}>{tab.content}</VStack>}
			</TabPanel>
		</div>
	);
};
