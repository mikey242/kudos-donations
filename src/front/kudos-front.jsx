/**
 * Kudos Render
 *
 *  @see https://stackoverflow.com/questions/42274721/shadow-dom-and-reactjs
 */

import React from 'react';
import KudosForm from './components/KudosForm';
import Message from './components/Message';
import { KudosButton } from './components/KudosButton';
import CampaignProvider from './contexts/CampaignContext';
import './kudos-fonts.css';
import './kudos-front.css';
import { createRoot } from '@wordpress/element';

document.addEventListener('DOMContentLoaded', function () {
	// Select the web components as target for render.
	const forms = document.querySelectorAll('.kudos-form');
	const messages = document.querySelectorAll('.kudos-message');
	const buttons = document.querySelectorAll('.kudos-button');

	// Kudos Donations buttons
	buttons.forEach((container) => {
		const root = createRoot(container);
		const campaignId = container.dataset.campaign;
		const targetId = container.dataset.target;
		const label = container.dataset.label;
		root.render(
			<CampaignProvider campaignId={campaignId}>
				<KudosButton targetId={targetId} children={label} />
			</CampaignProvider>
		);
	});

	// Kudos Donations form/modal
	forms.forEach((container) => {
		const root = createRoot(container);
		const campaignId = container.dataset.campaign;
		const displayAs = container.dataset.displayAs ?? 'button';
		root.render(
			<CampaignProvider campaignId={campaignId}>
				<KudosForm displayAs={displayAs} />
			</CampaignProvider>
		);
	});

	// Kudos Donations message
	messages.forEach((container) => {
		const root = createRoot(container);
		const title = container.dataset.title;
		const body = container.dataset.body;
		const color = container.dataset.color;
		root.render(<Message color={color} title={title} body={body} />);
	});
});
