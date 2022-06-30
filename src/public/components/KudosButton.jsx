import { KudosLogo } from '../../common/components/KudosLogo';
import React from 'react';
import { Button } from '../../common/components/controls';
import classNames from 'classnames';

const KudosButton = ({ children, className, onClick = null }) => {
	return (
		<div
			id="kudos-button"
			className={classNames('font-sans p-2', className)}
		>
			<Button
				onClick={() => onClick && onClick()}
				className="bg-primary logo-animate"
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
