import React from 'react';
import { FlagIcon } from '@heroicons/react/20/solid';

const ProgressBar = ({
	percentage,
	goal,
	total = 0,
	extra = 0,
	showGoal = true,
}) => {
	const extraPercentage = goal ? extra / (goal - total) : 0;

	return (
		<div className="w-full text-base">
			<div
				data-total={total}
				// data-goal={goal}
				className="h-7 border-1 border-solid border-gray-300 flex relative shadow-inner overflow-hidden bg-gray-200 rounded w-full"
			>
				<div
					style={{ width: percentage + '%' }}
					className="flex flex-shrink-0 justify-center items-center"
				>
					<div className="h-full w-full bg-green-500" />
					<div className="left-0 transition-opacity absolute flex items-center justify-center w-full opacity-0" />
					<div className="absolute right-1/2 translate-x-1/2">
						{percentage + '%' + ' ( €' + total + ')'}
					</div>
				</div>
				<div
					style={{ transform: `scaleX(${extraPercentage})` }}
					className="h-full w-full bg-green-500 transition-transform opacity-30 origin-left"
				></div>
				{showGoal && (
					<div className="kudos-progress-total flex space-x-2 items-center absolute top-1/2 right-0 mr-2 -translate-y-2/4">
						<FlagIcon className="w-4 h-4" />
						<span>€{goal}</span>
					</div>
				)}
			</div>
		</div>
	);
};

export { ProgressBar };
