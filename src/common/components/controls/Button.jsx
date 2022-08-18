import classNames from 'classnames';
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
				return typeof onClick === 'function' && onClick();
			}
		};

		const classes = classNames(
			className,
			isDisabled && 'cursor-not-allowed opacity-75',
			isOutline
				? 'border-primary border text-primary'
				: 'border-none text-white',
			isSmall ? 'px-2 py-2 text-sm' : 'px-3 py-2 sm:py-3',
			'relative font-bold focus:ring z-1 group cursor-pointer overflow-hidden rounded-lg flex justify-center items-center transition ease-in-out focus:ring-primary focus:ring-offset-2'
		);

		const inner = () => (
			<>
				{children}
				<div
					className={classNames(
						isOutline ? 'bg-none' : 'bg-primary',
						'absolute -z-1 w-full h-full top-0 left-0 group-hover:brightness-90 transition ease-in-out'
					)}
				/>
			</>
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
						disabled={isDisabled}
						className={classes}
						style={style}
						aria-label={ariaLabel}
					>
						{inner()}
					</button>
				)}
			</>
		);
	}
);

export { Button };
