import {
	TabPanel,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { useAdminQueryParams } from '../hooks';
import type { ReactNode } from 'react';

export interface AdminTab {
	name: string;
	title: string;
	content: ReactNode;
}

interface AdminTabPanelProps {
	tabs: AdminTab[];
}

export const AdminTabPanel = ({ tabs }: AdminTabPanelProps): ReactNode => {
	const { params, updateParams } = useAdminQueryParams();

	return (
		<div className="kudos-settings-tab-panel">
			<TabPanel
				initialTabName={params.tab || tabs[0]?.name}
				onSelect={(tab) => void updateParams({ tab })}
				tabs={tabs}
			>
				{(tab) => <VStack spacing={4}>{tab.content}</VStack>}
			</TabPanel>
		</div>
	);
};
