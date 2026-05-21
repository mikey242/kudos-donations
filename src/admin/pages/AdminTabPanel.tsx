import {
	TabPanel,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { useAdminQueryParams } from '../hooks';
import type { ReactNode } from 'react';
import { useEffect, useRef } from '@wordpress/element';
import { PanelNameContext } from '../components';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

export interface AdminPanel {
	name: string;
	content: ReactNode;
}

export interface AdminTab {
	name: string;
	title: string;
	panels: AdminPanel[];
}

interface AdminTabPanelProps {
	tabs: AdminTab[];
}

export const AdminTabPanel = ({ tabs }: AdminTabPanelProps): ReactNode => {
	const { params, updateParams } = useAdminQueryParams();
	const { createWarningNotice } = useDispatch(noticesStore);
	const isMounted = useRef(false);
	const tabExists =
		!params.tab || tabs.some((tab: AdminTab) => tab.name === params.tab);

	useEffect(() => {
		if (!tabExists) {
			createWarningNotice(
				// translators: %s is the name of the tab.
				sprintf(__('Cannot find tab "%s"'), params.tab)
			);
		}
	}, [createWarningNotice, params.tab, tabExists]);

	return (
		<div className="kudos-settings-tab-panel">
			<TabPanel
				initialTabName={params.tab || tabs[0]?.name}
				onSelect={(tab) => {
					if (isMounted.current) {
						void updateParams({ tab, panel: null });
					} else {
						isMounted.current = true;
						void updateParams({ tab });
					}
				}}
				tabs={tabs}
			>
				{(tab) => (
					<VStack spacing={4}>
						{tab.panels?.map(({ name, content }) => (
							<PanelNameContext.Provider key={name} value={name}>
								{content}
							</PanelNameContext.Provider>
						))}
					</VStack>
				)}
			</TabPanel>
		</div>
	);
};
