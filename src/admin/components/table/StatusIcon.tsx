import React from 'react';
import { Dashicon } from '@wordpress/components';

type IconKey = React.ComponentProps<typeof Dashicon>['icon'];

export interface StatusConfig {
	title: string;
	icon: string;
}

interface StatusIconProps {
	status: string;
	config: Record<string, StatusConfig>;
}

export const StatusIcon = ({
	status,
	config,
}: StatusIconProps): React.ReactNode => {
	const entry = config[status];
	return entry ? (
		<Dashicon title={entry.title} icon={entry.icon as IconKey} />
	) : null;
};
