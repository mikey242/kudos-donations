import React from 'react';
import {
	TabPanel,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useAdminQueryParams } from '../hooks';

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
	const { params, updateParams } = useAdminQueryParams();
	const initialTab = params.tab ?? tabs[0]?.name;
	const [selectedTab, setSelectedTab] = useState(initialTab);

	const updateTab = (tab: string) => {
		if (tab !== selectedTab) {
			setSelectedTab(tab);
			void updateParams({ tab });
		}
	};

	return (
		<div className="kudos-settings-tab-panel">
			<TabPanel
				initialTabName={selectedTab}
				onSelect={updateTab}
				tabs={tabs}
			>
				{(tab) => <VStack spacing={4}>{tab.content}</VStack>}
			</TabPanel>
		</div>
	);
};
