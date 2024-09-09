// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import React from 'react';
import { FormRouter } from './FormRouter';
import { KudosModal } from './KudosModal';
import Render from './Render';
import { useCampaignContext } from '../contexts/CampaignContext';

function KudosForm({ displayAs }) {
	const { campaign, campaignErrors } = useCampaignContext();
	const [timestamp, setTimestamp] = useState(0);
	const [formError, setFormError] = useState(null);
	const [formState, setFormState] = useState(null);
	const [isModalOpen, setIsModalOpen] = useState(false);
	const isForm = displayAs === 'form';
	const isModal = displayAs === 'button';

	useEffect(() => {
		setTimestamp(Date.now());
	}, []);

	useEffect(() => {
		if (!isModalOpen) {
			resetForm();
		}
	}, [isModalOpen]);

	const toggleModal = () => {
		setIsModalOpen(!isModalOpen);
	};

	const resetForm = () => {
		setFormState((prev) => ({
			...prev,
			currentStep: 1,
			formData: {},
		}));
	};

	async function submitForm(data) {
		setFormError(null);
		const formData = new window.FormData();
		formData.append('timestamp', timestamp.toString());
		formData.append('campaign_id', campaign.id);
		formData.append(
			'return_url',
			campaign.use_custom_return_url
				? campaign.custom_return_url
				: window.location.href
		);
		for (const key in data) {
			if (key === 'field') {
				formData.append(key, data[key][1]);
			} else {
				formData.append(key, data[key]);
			}
		}

		return apiFetch({
			path: '/kudos/v1/payment/create',
			method: 'POST',
			body: new URLSearchParams(formData),
		})
			.then((result) => {
				if (result.success) {
					window.location.href = result.url;
				} else {
					setFormError(result.data.message);
				}
				return result;
			})
			.catch((error) => {
				setFormError(error.message);
				return error;
			});
	}

	const renderDonationForm = () => (
		<>
			{formError && (
				<small className="text-center block font-normal mb-4 text-sm text-red-500">
					{formError}
				</small>
			)}
			<FormRouter
				step={formState?.currentStep ?? 1}
				campaign={campaign}
				setFormState={setFormState}
				submitForm={submitForm}
			/>
		</>
	);

	return (
		<Render
			themeColor={campaign?.meta?.theme_color}
			style={campaign?.meta?.custom_styles}
			errors={isForm && campaignErrors}
		>
			<>
				{isForm && renderDonationForm()}
				{isModal && (
					<>
						<KudosModal
							toggleModal={toggleModal}
							isOpen={isModalOpen}
						>
							{renderDonationForm()}
						</KudosModal>
					</>
				)}
			</>
		</Render>
	);
}

export default KudosForm;
