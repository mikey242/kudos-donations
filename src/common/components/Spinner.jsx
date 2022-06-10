import React from 'react';
import { KudosLogo } from './KudosLogo';

function Spinner({}) {
	return (
		<>
			<KudosLogo
				lineColor="#000"
				heartColor="#000"
				className="z-1 animate-spin w-6 h-6"
			/>
			<div className="absolute w-full h-full" />
		</>
	);
}

export { Spinner };
