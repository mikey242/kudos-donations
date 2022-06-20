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
widgets.forEach((root) => {
	const buttonLabel = root.dataset.label;
	const campaignId = root.dataset.campaign;
	const displayAs = root.dataset.displayAs;
	render(
		<KudosDonate
			root={root}
			campaignId={campaignId}
			buttonLabel={buttonLabel}
			displayAs={displayAs}
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
		<Message root={message} color={color} title={title} body={body} />,
		message
	);
});
