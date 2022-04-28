import { KudosLogo } from './KudosLogo';
import React from 'react';
import { Button } from '../../common/components/controls';
import classNames from 'classnames';

const KudosButton = ({ children, color, className, onClick = null }) => {
	return (
		<div id={'kudos-button'} className={classNames('font-sans', className)}>
			<Button
				onClick={() => onClick && onClick()}
				className={!color && 'bg-primary '}
				color={color}
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

export { KudosButton };
