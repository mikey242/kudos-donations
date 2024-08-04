import React from 'react';
import { forwardRef } from '@wordpress/element';
import { clsx } from 'clsx';
import { Help } from './controls/Field';

export const Panel = forwardRef(
	({ children, title, help, className = '' }, ref) => {
		return (
			<Pane className={clsx(className, 'mt-5')} ref={ref}>
				{(title || help) && (
					<div className="py-4 px-6 border-1 border-b border-gray-300">
						{title && <h3>{title}</h3>}
						{help && <Help>{help}</Help>}
					</div>
				)}
				<div className="p-6 space-y-8">{children}</div>
			</Pane>
		);
	}
);

export const Pane = forwardRef(({ children, className }, ref) => {
	return (
		<div
			ref={ref}
			className={clsx(
				className,
				'overflow-x-auto bg-white shadow-sm sm:rounded-lg z-1 ring-1 ring-black ring-opacity-5'
			)}
		>
			{children}
		</div>
	);
});
