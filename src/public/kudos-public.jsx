/**
 * Kudos Render
 *
 *  @see https://stackoverflow.com/questions/42274721/shadow-dom-and-reactjs
 */

import { render } from '@wordpress/element';
import React from 'react';
import KudosDonate from './components/KudosDonate';
import Message from './components/Message';

// Select the web components as target for render.
const widgets = document.querySelectorAll('.kudos-form');
const messages = document.querySelectorAll('.kudos-message');

// Kudos Donations form/modal
widgets.forEach((container) => {
	const buttonLabel = container.dataset.label;
	const campaignId = container.dataset.campaign;
	const displayAs = container.dataset.displayAs;
	render(
		<KudosDonate
			container={container}
			campaignId={campaignId}
			buttonLabel={buttonLabel}
			displayAs={displayAs}
		/>,
		container
	);
});

// Kudos Donations message
messages.forEach((message) => {
	const title = message.dataset.title;
	const body = message.dataset.body;
	const color = message.dataset.color;
	render(
		<Message root={message} color={color} title={title} body={body} />,
		message
	);
});
