import { useEffect, useRef, useState } from '@wordpress/element';
import React from 'react';
import { KudosModal } from './KudosModal';
import Render from './Render';
import { Button } from './controls';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Spinner } from './Spinner';
import { useCampaignContext } from '../contexts/CampaignContext';

export const PaymentStatus = ({ transactionId }) => {
	const { campaign, isLoading } = useCampaignContext();
	const [title, setTitle] = useState('');
	const [body, setBody] = useState(<Spinner />);
	const pollingRef = useRef(null);
	const intervalTime = 1000;
	const maxAttempts = 10;

	// Function to replace placeholders in a message template
	const replacePlaceholders = (template, data) => {
		return template.replace(
			/\{\{(.*?)}}/g,
			(_, key) => data[key.trim()] || ''
		);
	};

	useEffect(() => {
		if (!transactionId || !campaign) {
			return;
		}

		let attempts = 1;

		// Check transaction status.
		const checkTransactionStatus = async () => {
			try {
				const transaction = await apiFetch({
					path: `/wp/v2/kudos_transaction/${transactionId}`,
				});

				switch (transaction.meta.status) {
					case 'paid':
						const placeholders = {
							value:
								window.kudos.currencies[
									transaction.meta.currency
								] + transaction.meta.value,
							name: transaction.donor.meta.name,
						};
						setTitle(campaign.meta.return_message_title);
						setBody(
							replacePlaceholders(
								campaign.meta.return_message_text,
								placeholders
							)
						);
						clearInterval(pollingRef.current);
						break;
					case 'failed':
						setTitle(__('Payment Failed', 'kudos-donations'));
						setBody(__('Your payment failed.', 'kudos-donations'));
						clearInterval(pollingRef.current);
						break;
					case 'cancelled':
						setTitle(__('Payment Cancelled', 'kudos-donations'));
						setBody(
							__('Your payment was cancelled.', 'kudos-donations')
						);
						clearInterval(pollingRef.current);
						break;
				}
			} catch (error) {
				setTitle('Error');
				setBody(error.message);
				clearInterval(pollingRef.current);
			}
		};

		// Create polling interval.
		pollingRef.current = setInterval(() => {
			if (attempts < maxAttempts) {
				checkTransactionStatus().then(() => {
					attempts++;
				});
			} else {
				setTitle(__('Payment Pending', 'kudos-donations'));
				setBody(
					__(
						'Payment could not be verified, please contact us for more information.',
						'kudos-donations'
					)
				);
				clearInterval(pollingRef.current);
			}
		}, intervalTime);

		return () => clearInterval(pollingRef.current);
	}, [transactionId, campaign]);

	if (isLoading) {
		return;
	}

	return (
		<Message
			title={title}
			body={body}
			color={campaign?.meta?.theme_color}
			style={campaign?.meta?.custom_styles}
			dismissible={pollingRef.current}
		/>
	);
};

export default function Message({
	title,
	body,
	style = '',
	color = '#ff9f1c',
	dismissible = false,
}) {
	const [ready, setReady] = useState(false);
	const [modalOpen, setModalOpen] = useState(true);

	const closeModal = () => {
		setModalOpen(!dismissible);
	};

	useEffect(() => {
		setReady(true);
	}, []);

	return (
		<>
			{ready && (
				<Render style={style} themeColor={color}>
					<KudosModal toggleModal={closeModal} isOpen={modalOpen}>
						<>
							{title && (
								<h2 className="font-bold font-heading text-4xl/4 m-0 mb-2 block text-center">
									{title}
								</h2>
							)}
							{body && (
								<div className="text-lg text-center block font-normal mb-4">
									{body}
								</div>
							)}
							{dismissible && (
								<Button
									type="button"
									className="text-base block ml-auto"
									ariaLabel={__('Close', 'kudos-donations')}
									onClick={closeModal}
								>
									<span className="mx-2">OK</span>
								</Button>
							)}
						</>
					</KudosModal>
				</Render>
			)}
		</>
	);
}
