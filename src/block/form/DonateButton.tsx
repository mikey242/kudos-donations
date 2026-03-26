import { KudosLogo } from '../components';
import React from 'react';
import { Button } from '../controls';

interface DonateButtonProps {
	onClick?: () => void;
	children: React.ReactNode;
	showLogo?: boolean;
}

const DonateButton = ({
	children,
	onClick,
	showLogo = true,
}: DonateButtonProps) => {
	return (
		<Button
			id="donate-button"
			onClick={() => onClick && onClick()}
			className="logo-animate text-base"
		>
			{showLogo && (
				<div className="mr-3 flex text-white">
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
