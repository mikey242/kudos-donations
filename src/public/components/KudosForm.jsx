// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import React from 'react';
import FormRouter from './FormRouter';
import { checkRequirements } from '../../common/helpers/form';
import { Spinner } from '../../common/components/Spinner';
import KudosModal from '../../common/components/KudosModal';
import Render from '../../common/components/Render';
import { useCampaignContext } from '../../admin/contexts/CampaignContext';

function KudosForm({ displayAs }) {
	const { campaignRequest, campaignId, campaignErrors } =
		useCampaignContext();
	const [timestamp, setTimestamp] = useState(0);
	const [formError, setFormError] = useState(null);
	const [formState, setFormState] = useState(null);
	const [isModalOpen, setIsModalOpen] = useState(false);
	const { campaign } = campaignRequest;
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

	const handlePrev = () => {
		const { currentStep } = formState;
		if (currentStep === 1) return;
		let step = currentStep - 1;
		const state = { ...formState?.formData, ...campaign };

		// Find next available step.
		while (!checkRequirements(state, step) && step >= 1) {
			step--;
		}
		setFormState((prev) => ({
			...prev,
			currentStep: step,
		}));
	};

	const handleNext = (data, step) => {
		const state = { ...data, ...campaign };

		// Find next available step.
		while (!checkRequirements(state, step) && step <= 10) {
			step++;
		}

		setFormState((prev) => ({
			...prev,
			formData: { ...prev?.formData, ...data },
			currentStep: step,
		}));
	};

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
		formData.append('campaign_id', campaignId);
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
		}).then((result) => {
			if (result.success) {
				window.location.href = result.data;
			} else {
				setFormError(result.data.message);
			}
			return result;
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
				handleNext={handleNext}
				handlePrev={handlePrev}
				submitForm={submitForm}
			/>
		</>
	);

	return (
		<Render
			themeColor={campaign?.theme_color}
			errors={isForm && campaignErrors}
		>
			{/* If API not loaded yet then show a spinner */}
			{campaignRequest.ready ? (
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
			) : (
				<Spinner />
			)}
		</Render>
	);
}

export default KudosForm;
