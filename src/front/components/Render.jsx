import React from 'react';
import ReactShadowRoot from 'react-shadow-root';
import { clsx } from 'clsx';
import { useRef, useState } from '@wordpress/element';

function Render({
	children,
	themeColor,
	style,
	className,
	fonts,
	errors = null,
}) {
	// Set ready = false if there are stylesheets to load
	const [ready, setReady] = useState(!window.kudos?.stylesheets);
	// Count number of stylesheets to load
	const numSheetsRef = useRef(window.kudos?.stylesheets?.length);

	const updateLoadedSheets = () => {
		numSheetsRef.current--;
		// If all sheets loaded set ready to true
		if (numSheetsRef.current === 0) {
			setReady(true);
		}
	};

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
		<ReactShadowRoot>
			{/* Load global styles */}
			{window.kudos?.styles && <style>{window.kudos?.styles}</style>}

			{/* Load the main stylesheet */}
			{window.kudos?.stylesheets?.map((stylesheet, i) => (
				<link
					key={i}
					rel="stylesheet"
					onLoad={updateLoadedSheets}
					href={stylesheet}
				/>
			))}

			{/* Insert campaign-scoped custom CSS */}
			{style && <style>{style}</style>}

			{/* Fonts */}
			<style>
				{`:host {--kudos-font-heading: ${
					fonts?.header ?? 'cabinbold, sans-serif'
				} }`}
				{`:host {--kudos-font-body: ${
					fonts?.header ?? 'montserratregular, sans-serif'
				} }`}
			</style>

			{/* Set the theme color */}
			{themeColor && (
				<style>{`:host {--kudos-theme-primary: ${themeColor}`}</style>
			)}

			<div id="kudos-container">
				<div className={clsx(className, 'font-body')}>
					{ready && !errors ? <>{children}</> : <>{renderErrors()}</>}
				</div>
			</div>
		</ReactShadowRoot>
	);
}

export default Render;