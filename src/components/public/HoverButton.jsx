import { KudosLogo } from '../KudosLogo';
import React from 'react';
import { Button } from '../controls';
import classNames from 'classnames';

const HoverButton = ({ color, className, onClick = null }) => {
	return (
		<div
			id="kudos-badge"
			className={classNames(
				'font-sans p-2 fixed bottom-4 right-4',
				className
			)}
		>
			<Button
				onClick={() => onClick && onClick()}
				className={classNames(
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
