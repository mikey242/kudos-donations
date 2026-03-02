import React from 'react';
import { Button, Flex } from '@wordpress/components';
import { useAdminQueryParams } from '../hooks';
import { AdminPages } from './AdminRouter';

export const AdminMenu = (): React.ReactNode => {
	const { params, setParams } = useAdminQueryParams();
	const { page: currentView } = params;

	const changePage = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
		void setParams({ page: e.currentTarget?.value });
	};

	return (
		<div className="kudos-admin-menu">
			<Flex className="admin-wrap" justify="center" align="center">
				{AdminPages.map(({ label, view, icon }) => {
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
