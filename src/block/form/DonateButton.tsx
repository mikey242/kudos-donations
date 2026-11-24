import { KudosLogo } from '../components';
import React from 'react';
import { Button } from '../controls';
import clsx from 'clsx';
import { __ } from '@wordpress/i18n';

interface DonateButtonProps {
	onClick?: () => void;
	children: React.ReactNode;
	showLogo?: boolean;
	shape?: 'pill' | 'classic';
	ariaLabel?: string;
}

const DonateButton = ({
	children,
	onClick,
	showLogo = true,
	shape = 'classic',
	ariaLabel,
}: DonateButtonProps) => {
	const hasLabel =
		typeof children === 'string' ? children.trim() !== '' : !!children;

	return (
		<Button
			id="donate-button"
			onClick={onClick}
			isRounded={shape === 'pill'}
			className="logo-animate text-base"
			ariaLabel={
				hasLabel
					? undefined
					: (ariaLabel ?? __('Donate', 'kudos-donations'))
			}
		>
			{showLogo && (
				<div
					className={clsx('flex text-white', hasLabel && 'mr-3')}
					aria-hidden="true"
				>
					<KudosLogo
						className="w-5 h-5"
						lineColor="currentColor"
						heartColor="currentColor"
					/>
				</div>
			)}
			{hasLabel && children}
		</Button>
	);
};

export { DonateButton };
