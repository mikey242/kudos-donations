import React from 'react';
import { KudosLogo } from './KudosLogo';

function Spinner({}) {
	return (
		<>
			<KudosLogo
				lineColor="#000"
				heartColor="#000"
				className="z-1 animate-spin"
				style={{ width: '1.5rem', height: '1.5rem' }}
			/>
		</>
	);
}

export { Spinner };
