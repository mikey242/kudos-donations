/**
 * Kudos Render
 *
 *  @see https://stackoverflow.com/questions/42274721/shadow-dom-and-reactjs
 */

import React from 'react';
import { KudosForm } from './components/KudosForm';
import Message, { PaymentStatus } from './components/Message';
import CampaignProvider from './contexts/CampaignContext';
import './kudos-fonts.css';
import './kudos-front.css';
import { createRoot } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

domReady(() => {
	// Select the web components as target for render.
	const forms = document.querySelectorAll('.kudos-form');
	const messages = document.querySelectorAll('.kudos-message');
	const status = document.querySelectorAll('.kudos-transaction-status');

	// Kudos Donations form/modal
	forms.forEach((container) => {
		if (!container.shadowRoot) {
			const root = createRoot(container);
			const options = JSON.parse(container.dataset?.options);
			root.render(
				<CampaignProvider campaignId={options?.campaign_id}>
					<KudosForm
						label={options?.button_label}
						displayAs={options?.type ?? 'button'}
						alignment={options?.alignment ?? 'left'}
					/>
				</CampaignProvider>
			);
		}
	});

	// Kudos Payment Status
	status.forEach((container) => {
		if (!container.shadowRoot) {
			const root = createRoot(container);
			const transactionId = container.dataset.transaction;
			const campaignId = container.dataset.campaign;
			root.render(
				<CampaignProvider campaignId={campaignId}>
					<PaymentStatus transactionId={transactionId} />
				</CampaignProvider>
			);
		}
	});

	// Kudos Donations message
	messages.forEach((container) => {
		if (!container.shadowRoot) {
			const root = createRoot(container);
			const title = container.dataset.title;
			const body = container.dataset.body;
			const color = container.dataset.color;
			root.render(<Message color={color} title={title} body={body} />);
		}
	});
});
