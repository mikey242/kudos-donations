import React from 'react';
import type { IconType } from '@wordpress/components';
import { Button, Flex } from '@wordpress/components';
import { useAdminQueryParams } from '../hooks';
import { __ } from '@wordpress/i18n';

interface NavItem {
	label: string;
	view: string;
	icon?: IconType;
}

const navItems: NavItem[] = [
	{
		label: __('Campaigns', 'kudos-donations'),
		view: 'kudos-campaigns',
		icon: 'megaphone',
	},
	{
		label: __('Transactions', 'kudos-donations'),
		view: 'kudos-transactions',
		icon: 'money-alt',
	},
	{
		label: __('Subscriptions', 'kudos-donations'),
		view: 'kudos-subscriptions',
		icon: 'update',
	},
	{
		label: __('Donors', 'kudos-donations'),
		view: 'kudos-donors',
		icon: 'groups',
	},
	{
		label: __('Settings', 'kudos-donations'),
		view: 'kudos-settings',
		icon: 'admin-settings',
	},
];

export const AdminMenu = (): React.ReactNode => {
	const { params, setParams } = useAdminQueryParams();
	const { page: currentView } = params;

	const changePage = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
		void setParams({ page: e.currentTarget?.value });
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
