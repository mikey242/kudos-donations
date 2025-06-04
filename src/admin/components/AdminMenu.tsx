import React from 'react';
import type { IconType } from '@wordpress/components';
import { Button, Flex } from '@wordpress/components';
import { useAdminQueryParams } from '../hooks';

interface NavItem {
	label: string;
	view: string;
	icon?: IconType;
}

const navItems: NavItem[] = [
	{ label: 'Campaigns', view: 'kudos-campaigns', icon: 'megaphone' },
	{
		label: 'Transactions',
		view: 'kudos-transactions',
		icon: 'money-alt',
	},
	{
		label: 'Subscriptions',
		view: 'kudos-subscriptions',
		icon: 'update',
	},
	{ label: 'Donors', view: 'kudos-donors', icon: 'groups' },
	{ label: 'Settings', view: 'kudos-settings', icon: 'admin-settings' },
];

export const AdminMenu = (): React.ReactNode => {
	const { params } = useAdminQueryParams();
	const { page: currentView } = params;

	return (
		<div className="kudos-admin-menu">
			<Flex className="admin-wrap" justify="center" align="center">
				{navItems.map(({ label, view, icon }) => {
					const isActive = currentView === view;
					return (
						<Button
							style={{ textDecoration: 'none' }}
							key={view}
							variant="link"
							icon={icon ?? 'marker'}
							className={isActive ? 'is-active' : ''}
							href={`?page=${view}`}
						>
							{label}
						</Button>
					);
				})}
			</Flex>
		</div>
	);
};
