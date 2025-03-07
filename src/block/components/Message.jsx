import { useEffect, useState } from '@wordpress/element';
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
	const [isDone, setIsDone] = useState(false);
	const [attempts, setAttempts] = useState(1);

	useEffect(() => {
		if (!transactionId) {
			return;
		}

		let isMounted = true;
		const maxAttempts = 5;
		const intervalTime = 2000;

		// Retry or mark as finished
		const retryOrFinish = () => {
			if (attempts < maxAttempts) {
				setAttempts((prev) => prev + 1);
				setTimeout(checkTransactionStatus, intervalTime);
			} else {
				setTitle(__('Payment Pending', 'kudos-donations'));
				setBody(
					__(
						'Payment could not be verified, please contact us for more information.',
						'kudos-donations'
					)
				);
				setIsDone(true);
			}
		};

		// Check transaction status
		const checkTransactionStatus = async () => {
			if (!isMounted) {
				return;
			}

			const transaction = await apiFetch({
				path: `/wp/v2/kudos_transaction/${transactionId}`,
			});

			switch (transaction.meta.status) {
				case 'paid':
					setTitle(campaign.meta.return_message_title);
					setBody(campaign.meta.return_message_text);
					setIsDone(true);
					break;

				case 'cancelled':
					setTitle(__('Payment Cancelled', 'kudos-donations'));
					setBody(
						__('Your payment was cancelled.', 'kudos-donations')
					);
					setIsDone(true);
					break;

				default:
					retryOrFinish();
					break;
			}
		};

		setTimeout(checkTransactionStatus, 1000);

		return () => {
			isMounted = false;
		};
	}, [attempts, campaign, transactionId]);

	if (isLoading) {
		return;
	}

	return (
		<Message
			title={title}
			body={body}
			color={campaign?.meta?.theme_color}
			style={campaign?.meta?.custom_styles}
			dismissible={isDone}
		/>
	);
};

export default function Message({
	color,
	style,
	title,
	body,
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
				<Render
					style={style}
					themeColor={color}
					className={className}
					fonts={fonts}
				>
					<KudosModal toggleModal={closeModal} isOpen={modalOpen}>
						<>
							{title && (
								<h2 className="font-bold font-heading text-4xl/4 m-0 mb-2 block text-center">
									{title}
								</h2>
							)}
							{body && (
								<p className="text-lg text-center block font-normal mb-4">
									{body}
								</p>
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
