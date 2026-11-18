import { KudosLogo } from '../components';
import React from 'react';
import { Button } from '../controls';
import clsx from 'clsx';

interface DonateButtonProps {
	onClick?: () => void;
	children: React.ReactNode;
	showLogo?: boolean;
	shape?: 'pill' | 'classic';
}

const DonateButton = ({
	children,
	onClick,
	showLogo = true,
	shape = 'classic',
}: DonateButtonProps) => {
	return (
		<Button
			id="donate-button"
			onClick={() => onClick && onClick()}
			isRounded={shape === 'pill'}
			className="logo-animate text-base"
		>
			{showLogo && (
				<div className={clsx('flex text-white', children && 'mr-3')}>
					<KudosLogo
						className="w-5 h-5"
						lineColor="currentColor"
						heartColor="currentColor"
					/>
				</div>
			)}
			{children}
		</Button>
	);
};

export { DonateButton };
