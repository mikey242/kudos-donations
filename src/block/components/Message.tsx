import { useEffect, useRef, useState } from '@wordpress/element';
import React, { ReactNode } from 'react';
import { KudosModal } from './KudosModal';
import Render from './Render';
import { Button } from './controls';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Spinner } from './Spinner';
import { useCampaignContext } from '../contexts';

interface PaymentStatusProps {
	transactionId: string;
}
interface KudosPayment {
	data: {
		status: 'paid' | 'failed' | 'cancelled';
		value: string;
		currency: string;
		name: string;
	};
}

export const PaymentStatus = ({ transactionId }: PaymentStatusProps) => {
	const { campaign, isLoading } = useCampaignContext();
	const [title, setTitle] = useState<string>('');
	const [body, setBody] = useState<ReactNode>(<Spinner />);
	const pollingRef = useRef<NodeJS.Timeout>(null);
	const intervalTime = 1000;
	const maxAttempts = 10;

	// Function to replace placeholders in a message template
	const replacePlaceholders = (
		template: string,
		data: Record<string, string>
	) => {
		return template.replace(
			/\{\{(.*?)}}/g,
			(_, key) => data[key.trim()] || ''
		);
	};

	useEffect(() => {
		let attempts = 1;

		// Check transaction status.
		const checkTransactionStatus = async () => {
			try {
				const nonce = getNonceFromUrl();
				const response = (await apiFetch({
					path: `/kudos/v1/payment/status/?id=${transactionId}`,
					headers: {
						'X-Kudos-Nonce': nonce,
					},
				})) as KudosPayment;
				switch (response.data.status) {
					case 'paid':
						const placeholders = {
							value:
								window.kudos.currencies[
									response.data.currency
								] + response.data.value,
							name: response.data.name,
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
			dismissible={!!pollingRef.current}
		/>
	);
};

interface MessageProps {
	title: string;
	body: ReactNode;
	style?: string;
	color?: string;
	dismissible?: boolean;
}

export default function Message({
	title,
	body,
	style = '',
	color = '#ff9f1c',
	dismissible = true,
}: MessageProps) {
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

const getNonceFromUrl = () => {
	const params = new URLSearchParams(window.location.search);
	return params.get('kudos_nonce');
};
