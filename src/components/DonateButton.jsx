import { KudosLogo } from './KudosLogo';
import React from 'react';
import { Button } from './controls';
import { clsx } from 'clsx';

const DonateButton = ({
	children,
	className,
	onClick = null,
	color = null,
}) => {
	return (
		<div id="kudos-button" className={clsx('font-sans', className)}>
			<Button
				onClick={() => onClick && onClick()}
				className={clsx(!color && 'bg-primary', 'logo-animate')}
				style={{ backgroundColor: color }}
			>
				<div className="mr-3 flex text-white">
					<KudosLogo
						className="w-5 h-5"
						lineColor="currentColor"
						heartColor="currentColor"
					/>
				</div>
				{children}
			</Button>
		</div>
	);
};

export { DonateButton };
