import { KudosLogo } from './KudosLogo';
import React from 'react';
import { Button } from './controls';
import classNames from 'classnames';

const DonateButton = ({
	children,
	className,
	onClick = null,
	color = null,
}) => {
	return (
		<div id="kudos-button" className={classNames('font-sans', className)}>
			<Button
				onClick={() => onClick && onClick()}
				className={classNames(!color && 'bg-primary', 'logo-animate')}
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