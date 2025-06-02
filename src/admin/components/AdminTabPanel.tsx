import React from 'react';
import {
	TabPanel,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
} from '@wordpress/components';
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
	const { searchParams, setQueryParams } = useAdminContext();

	const updateTab = (tabName: string) => {
		setQueryParams({
			set: [{ name: 'tab', value: tabName }],
		});
	};

	return (
		<div className="kudos-settings-tab-panel">
			<TabPanel
				initialTabName={searchParams.get('tab')}
				onSelect={updateTab}
				tabs={tabs}
			>
				{(tab) => <VStack spacing={4}>{tab.content}</VStack>}
			</TabPanel>
		</div>
	);
};
