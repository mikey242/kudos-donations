import { clsx } from 'clsx';
import { useRef, useState } from '@wordpress/element';
import root from 'react-shadow';
import React, { ReactNode } from 'react';
import { Spinner } from './Spinner';
interface RenderProps {
	children: ReactNode;
	themeColor?: string;
	style?: string;
	className?: string;
	fonts?: {
		header?: string;
		body?: string;
	};
	alignment?: 'left' | 'center' | 'right';
	errors?: string[] | null;
	isContentReady?: boolean;
}

export const Render = ({
	children,
	themeColor,
	style,
	className,
	fonts,
	alignment,
	errors = null,
	isContentReady = true,
}: RenderProps) => {
	// Set ready = false if there are stylesheets to load
	const [ready, setReady] = useState(!window.kudos?.front?.stylesheets);
	// Count number of stylesheets to load
	const numSheetsRef = useRef(window.kudos?.front?.stylesheets?.length);

	const updateLoadedSheets = () => {
		numSheetsRef.current--;
		// If all sheets loaded set ready to true
		if (numSheetsRef.current === 0) {
			setReady(true);
		}
	};

	const alignmentClasses = {
		left: 'justify-start',
		center: 'justify-center',
		right: 'justify-end',
	};

	const alignmentResult = alignmentClasses[alignment];

	const renderErrors = () => (
		<>
			{errors && (
				<>
					<p className="m-0">Kudos Donations ran into a problem:</p>
					{errors.map((error, i) => (
						<p key={i} className="text-red-500">
							- {error}
						</p>
					))}
				</>
			)}
		</>
	);

	return (
		<root.div>
			{/* Load global styles */}
			{window.kudos?.front?.customStyles && (
				<style>{window.kudos?.front?.customStyles}</style>
			)}

			{/* Load the main stylesheet */}
			{window.kudos?.front?.stylesheets?.map((stylesheet, i) => (
				<link
					key={i}
					rel="stylesheet"
					onLoad={updateLoadedSheets}
					href={stylesheet}
				/>
			))}

			{/* Insert campaign-scoped custom CSS */}
			{style && <style>{style}</style>}

			{/* Fontsize/Font/Theme */}
			<style>
				{`:host { 
					font-size: ${window.kudos?.front?.baseFontSize ?? '1.2rem'};
					--kudos-font-heading: ${fonts?.header ?? 'cabinbold, sans-serif'} ;
					--kudos-font-body: ${fonts?.body ?? 'montserratregular, sans-serif'};
					--kudos-theme-primary: ${themeColor};
				}`}
			</style>

			<div
				className={clsx(
					className,
					'flex font-body text-gray-900',
					alignmentResult
				)}
			>
				{ready && !errors ? (
					<>{isContentReady ? children : <Spinner />}</>
				) : (
					<>{renderErrors()}</>
				)}
			</div>
		</root.div>
	);
};
