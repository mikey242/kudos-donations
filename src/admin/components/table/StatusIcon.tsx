import React from 'react';
import { Icon } from '@wordpress/components';
import type { IconType } from '@wordpress/components';

export interface StatusConfig {
	title: string;
	icon: IconType;
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
		<span
			role="img"
			aria-label={entry.title}
			title={entry.title}
			style={{ display: 'inline-flex' }}
		>
			<Icon icon={entry.icon} />
		</span>
	) : null;
};
