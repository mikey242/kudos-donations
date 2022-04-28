/**
 * Kudos Render
 *
 *  @see https://stackoverflow.com/questions/42274721/shadow-dom-and-reactjs
 */

import { render } from '@wordpress/element';
import React from 'react';
import KudosDonate from './components/KudosDonate';
import KudosMessage from './components/KudosMessage';

// Select the web components as target for render.
const roots = document.querySelectorAll('.kudos-form');
const messages = document.querySelectorAll('.kudos-message');

// Kudos Donations form/modal
roots.forEach((root) => {
	const buttonLabel = root.dataset.label;
	const campaignId = root.dataset.campaign;
	render(
		<KudosDonate
			root={root}
			campaignId={campaignId}
			buttonLabel={buttonLabel}
		/>,
		root
	);
});

// Kudos Donations message
messages.forEach((message) => {
	const title = message.dataset.title;
	const body = message.dataset.body;
	const color = message.dataset.color;
	render(
		<KudosMessage root={message} color={color} title={title} body={body} />,
		message
	);
});
