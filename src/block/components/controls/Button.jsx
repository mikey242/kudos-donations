import { clsx } from 'clsx';
import React from 'react';
import { forwardRef } from '@wordpress/element';

const Button = forwardRef(
	(
		{
			type = 'button',
			children,
			href,
			isOutline,
			isExternal,
			isDisabled,
			isSmall,
			isBusy,
			icon,
			form,
			ariaLabel,
			className,
			onClick,
			style,
		},
		ref
	) => {
		const handleClick = (e) => {
			if (href) {
				e.preventDefault();
				window.location.href = href;
			} else {
				return (
					typeof onClick === 'function' &&
					!isDisabled &&
					!isBusy &&
					onClick()
				);
			}
		};

		const classes = clsx(
			className,
			isDisabled && 'cursor-not-allowed opacity-75',
			isBusy && 'cursor-not-allowed',
			isOutline
				? 'border-primary border text-primary'
				: 'border-none text-white',
			isSmall ? 'px-2 py-2 text-sm' : 'px-5 py-3',
			'relative leading-none font-bold focus:ring-3 z-1 group cursor-pointer overflow-hidden rounded-lg flex justify-center items-center transition ease-in-out focus:ring-primary focus:ring-offset-2'
		);

		const inner = () => (
			<>
				{children}
				<div
					className={clsx(
						'button-background',
						isOutline ? 'bg-none' : 'bg-primary',
						'absolute -z-1 w-full h-full top-0 left-0 group-hover:brightness-90 transition ease-in-out'
					)}
				/>
			</>
		);

		const loader = () => (
			<svg
				className="animate-spin mr-2 h-5 w-5"
				xmlns="http://www.w3.org/2000/svg"
				fill="none"
				viewBox="0 0 24 24"
			>
				<circle
					className="opacity-25"
					cx="12"
					cy="12"
					r="10"
					stroke="currentColor"
					strokeWidth="4"
				/>
				<path
					className="opacity-75"
					fill="currentColor"
					d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
				/>
			</svg>
		);

		return (
			<>
				{href ? (
					<a
						href={href}
						target={isExternal && '_blank'}
						ref={ref}
						className={classes}
						aria-label={ariaLabel}
					>
						{inner()}
					</a>
				) : (
					<button
						type={type}
						onClick={handleClick}
						ref={ref}
						form={form}
						disabled={isDisabled || isBusy}
						className={classes}
						style={style}
						aria-label={ariaLabel}
					>
						{isBusy ? loader() : icon}
						{inner()}
					</button>
				)}
			</>
		);
	}
);

export { Button };
