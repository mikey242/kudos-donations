import React from 'react';
import ReactShadowRoot from 'react-shadow-root';
import classNames from 'classnames';
import { useRef, useState } from '@wordpress/element';

function Render({ children, themeColor, style, className }) {
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

	return (
		<ReactShadowRoot>
			{window.kudos?.stylesheets?.map((stylesheet, i) => (
				<link
					key={i}
					rel="stylesheet"
					onLoad={updateLoadedSheets}
					href={stylesheet}
				/>
			))}
			{style && <style>{style}</style>}
			{themeColor && (
				<style>{`:host {--kudos-theme-primary: ${themeColor}`}</style>
			)}
			{ready && (
				<div id="kudos-container">
					<div
						id="kudos"
						className={classNames(className, 'font-sans')}
					>
						{children}
					</div>
				</div>
			)}
		</ReactShadowRoot>
	);
}

export default Render;
