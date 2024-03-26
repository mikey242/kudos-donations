import { KudosLogo } from '../KudosLogo';
import React from 'react';
import { Button } from '../controls';
import { clsx } from 'clsx';

const HoverButton = ({ color, className, onClick = null }) => {
	return (
		<div
			id="kudos-badge"
			className={clsx('font-sans p-2 fixed bottom-4 right-4', className)}
		>
			<Button
				onClick={() => onClick && onClick()}
				className={clsx(
					!color && 'bg-primary',
					'rounded-full p-5 w-16 h-16'
				)}
				color={color}
			>
				<KudosLogo
					className="w-5 h-5"
					lineColor="currentColor"
					heartColor="currentColor"
				/>
			</Button>
		</div>
	);
};

export { HoverButton };
