import {
	TabPanel,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { useAdminQueryParams } from '../hooks';
import type { ReactNode } from 'react';
import React from 'react';
import { useEffect } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { applyFilters } from '@wordpress/hooks';

export interface AdminTab {
	name: string;
	title: string;
	content: ReactNode;
}

export interface AdminPanel {
	name: string;
	content: ReactNode;
}

interface PanelListProps {
	namespace: string;
	tabName: string;
	defaultPanels: AdminPanel[];
	args?: unknown;
}

export const PanelList = ({
	namespace,
	tabName,
	defaultPanels,
	args,
}: PanelListProps): ReactNode => {
	const panels = applyFilters(
		`${namespace}.${tabName}`,
		defaultPanels,
		...(args !== undefined ? [args] : [])
	) as AdminPanel[];

	return (
		<>
			{panels.map(({ name, content }) => (
				<React.Fragment key={name}>{content}</React.Fragment>
			))}
		</>
	);
};

interface AdminTabPanelProps {
	tabs: AdminTab[];
}

export const AdminTabPanel = ({ tabs }: AdminTabPanelProps): ReactNode => {
	const { params, updateParams } = useAdminQueryParams();
	const { createWarningNotice } = useDispatch(noticesStore);
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
				onSelect={(tab) => void updateParams({ tab })}
				tabs={tabs}
			>
				{(tab) => <VStack spacing={4}>{tab.content}</VStack>}
			</TabPanel>
		</div>
	);
};
