/**
 * Kudos Render
 *
 *  @see https://stackoverflow.com/questions/42274721/shadow-dom-and-reactjs
 */

import { render } from '@wordpress/element';
import React from 'react';
import KudosForm from './components/KudosForm';
import Message from './components/Message';
import { KudosButton } from './components/KudosButton';
import CampaignProvider from '../admin/contexts/CampaignContext';

// Select the web components as target for render.
const forms = document.querySelectorAll('.kudos-form');
const messages = document.querySelectorAll('.kudos-message');
const buttons = document.querySelectorAll('.kudos-button');

// Kudos Donations buttons
buttons.forEach((container) => {
	const campaignId = container.dataset.campaign;
	const targetId = container.dataset.target;
	const label = container.dataset.label;

	render(
		<CampaignProvider campaignId={campaignId}>
			<KudosButton
				// campaignId={campaignId}
				targetId={targetId}
				children={label}
			/>
		</CampaignProvider>,
		container
	);
});

// Kudos Donations form/modal
forms.forEach((container) => {
	const campaignId = container.dataset.campaign;
	const displayAs = container.dataset.displayAs;
	render(
		<CampaignProvider campaignId={campaignId}>
			<KudosForm displayAs={displayAs} />
		</CampaignProvider>,
		container
	);
});

// Kudos Donations message
messages.forEach((message) => {
	const title = message.dataset.title;
	const body = message.dataset.body;
	const color = message.dataset.color;
	render(<Message color={color} title={title} body={body} />, message);
});
