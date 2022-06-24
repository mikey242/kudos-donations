import React from 'react';
import ReactShadowRoot from 'react-shadow-root';
import classNames from 'classnames';
import { useState } from '@wordpress/element';

function Render({ children, themeColor, stylesheet, style, className }) {
	const [ready, setReady] = useState(false);
	return (
		<>
			<ReactShadowRoot>
				{stylesheet && (
					<link
						rel="stylesheet"
						onLoad={() => setReady(true)}
						href={stylesheet}
					/>
				)}
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
		</>
	);
}

export default Render;
