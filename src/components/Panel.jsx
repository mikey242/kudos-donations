import React from 'react';
import { forwardRef } from '@wordpress/element';

const Panel = forwardRef(({ children, title, className = '' }, ref) => {
	return (
		<div>
			{title && <h2 className="text-center my-5">{title}</h2>}
			<div
				ref={ref}
				className={`${className} mt-5 bg-white shadow-sm sm:rounded-lg z-1 ring-1 ring-black ring-opacity-5`}
			>
				{children}
			</div>
		</div>
	);
});

export default Panel;
