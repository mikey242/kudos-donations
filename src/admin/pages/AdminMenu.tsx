import { Button, Flex } from '@wordpress/components';
import { applyFilters } from '@wordpress/hooks';
import { useAdminQueryParams } from '../hooks';
import { defaultAdminPages, type PageConfig } from './AdminRouter';
import type { ReactNode, MouseEvent } from 'react';

export const AdminMenu = (): ReactNode => {
	const adminPages = applyFilters(
		'kudosAdminPages',
		defaultAdminPages
	) as PageConfig[];
	const { params, setParams } = useAdminQueryParams();
	const { page: currentView } = params;

	const changePage = (
		e: MouseEvent<HTMLButtonElement | HTMLAnchorElement>
	) => {
		void setParams({ page: (e.currentTarget as HTMLButtonElement).value });
	};

	return (
		<div className="kudos-admin-menu">
			<Flex className="admin-wrap" justify="center" align="center">
				{adminPages.map(({ label, view, icon }) => {
					const isActive = currentView === view;
					return (
						<Button
							style={{ textDecoration: 'none' }}
							key={view}
							variant="link"
							icon={icon ?? 'marker'}
							className={isActive ? 'is-active' : ''}
							onClick={changePage}
							value={view}
						>
							{label}
						</Button>
					);
				})}
			</Flex>
		</div>
	);
};
