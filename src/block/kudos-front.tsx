/**
 * Kudos Render
 *
 *  @see https://stackoverflow.com/questions/42274721/shadow-dom-and-reactjs
 */

/* eslint-disable camelcase */

import { KudosForm, KudosButtonAttributes } from './components';
import Message, { PaymentStatus } from './components/Message';
import './kudos-fonts.css';
import './kudos-front.css';
import { createRoot } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import React from 'react';
import CampaignProvider from './contexts/campaign-context';

domReady(() => {
	// Select the web components as target for render.
	const forms = document.querySelectorAll<HTMLElement>('.kudos-form');
	const messages = document.querySelectorAll<HTMLElement>('.kudos-message');
	const status = document.querySelectorAll<HTMLElement>(
		'.kudos-transaction-status'
	);

	// Kudos Donations form/modal
	forms.forEach((container) => {
		if (!container.shadowRoot) {
			const root = createRoot(container);
			const options: KudosButtonAttributes = JSON.parse(
				container.dataset?.options
			);
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
			const vendorId = container.dataset.vendorId;
			root.render(
				<CampaignProvider campaignId={campaignId}>
					<PaymentStatus
						transactionId={transactionId}
						vendorId={vendorId}
					/>
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
