import React from 'react';
import type { IconType } from '@wordpress/components';
import { Button, Flex } from '@wordpress/components';
import { useAdminContext } from './contexts';

interface NavItem {
	label: string;
	view: string;
	icon?: IconType;
}

export const AdminMenu = (): React.ReactNode => {
	const { searchParams, setQueryParams } = useAdminContext();
	const currentView = searchParams.get('view') ?? 'donors';

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
	const onClick = (e: React.MouseEvent, view: string) => {
		e.preventDefault();
		setQueryParams({
			reset: true,
			preserveKeys: ['page'],
			set: [{ name: 'view', value: view }],
		});
	};

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
							href={`?page=kudos-admin&view=${view}`}
							onClick={(e: React.MouseEvent) => onClick(e, view)}
						>
							{label}
						</Button>
					);
				})}
			</Flex>
		</div>
	);
};
