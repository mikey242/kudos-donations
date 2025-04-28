import { KudosLogo } from './KudosLogo';
import React from 'react';
import { Button } from './controls';

const DonateButton = ({ children, onClick = null }) => {
	return (
		<Button
			id="donate-button"
			onClick={() => onClick && onClick()}
			className="logo-animate text-base"
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
	);
};

export { DonateButton };
