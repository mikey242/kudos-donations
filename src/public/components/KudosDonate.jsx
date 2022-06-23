// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from '@wordpress/element';
import React from 'react';
import { KudosButton } from './KudosButton';
import FormRouter from './FormRouter';
import { checkRequirements } from '../../common/helpers/form';
import { Spinner } from '../../common/components/Spinner';
import KudosModal from '../../common/components/KudosModal';
import Render from '../../common/components/Render';

function KudosDonate({ buttonLabel, campaignId, displayAs, container }) {
	const [campaign, setCampaign] = useState();
	const [total, setTotal] = useState(0);
	const [timestamp, setTimestamp] = useState(0);
	const [isApiLoaded, setIsApiLoaded] = useState(false);
	const [errors, setErrors] = useState(null);
	const [formState, setFormState] = useState(null);
	const [modalOpen, setModalOpen] = useState(false);
	const targetRef = useRef(null);

	const toggleModal = () => {
		// Open modal
		if (!modalOpen) {
			setModalOpen(true);
		} else {
			// Close modal
			setModalOpen(false);
			setTimeout(() => {
				setFormState((prev) => ({
					...prev,
					currentStep: 1,
					formData: {},
				}));
			}, 300);
		}
	};

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

	async function submitForm(data) {
		setErrors([]);
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
				setErrors([result.data.message]);
			}
			return result;
		});
	}

	const getCampaign = () => {
		return apiFetch({
			path: `wp/v2/kudos_campaign/${campaignId}`,
			method: 'GET',
		})
			.then((response) => {
				setCampaign(response?.meta);
				setTimestamp(Date.now());
			})
			.catch((error) => {
				throw {
					message: `Failed to fetch campaign '${campaignId}'.`,
					original: error,
				};
			});
	};

	const getTotal = () => {
		return apiFetch({
			path: `kudos/v1/transaction/campaign/total/${campaignId}`,
			method: 'GET',
		}).then((response) => {
			setTotal(response);
		});
	};

	const getData = () => {
		Promise.all([getCampaign(), getTotal()])
			.then(() => setIsApiLoaded(true))
			.catch((error) => {
				setErrors([error.message]);
			});
	};

	const donationForm = () => (
		<>
			{errors?.length > 0 &&
				errors.map((e, i) => (
					<small
						key={i}
						className="text-center block font-normal mb-4 text-sm text-red-500"
					>
						{e}
					</small>
				))}
			<FormRouter
				ref={targetRef}
				step={formState?.currentStep ?? 1}
				campaign={campaign}
				total={total}
				handleNext={handleNext}
				handlePrev={handlePrev}
				submitForm={submitForm}
			/>
		</>
	);

	useEffect(() => {
		if (campaignId) {
			getData();
		} else {
			setErrors([__('No campaign ID', 'kudos-donations')]);
		}
	}, []);

	return (
		<>
			<Render
				container={container}
				themeColor={campaign?.theme_color}
				stylesheet="/wp-content/plugins/kudos-donations/build/public/kudos-public.css"
			>
				{/* If API not loaded yet then show a spinner */}
				{isApiLoaded ? (
					<>
						{displayAs === 'form' && donationForm()}
						{displayAs === 'button' && (
							<>
								<KudosButton onClick={toggleModal}>
									{buttonLabel}
								</KudosButton>
								<KudosModal
									toggle={toggleModal}
									container={container}
									isOpen={modalOpen}
								>
									{donationForm()}
								</KudosModal>
							</>
						)}
					</>
				) : (
					!errors?.length && <Spinner />
				)}
				{/* Show errors if present */}
				{errors?.length && (
					<>
						<p className="m-0">
							Kudos Donations ran into a problem:
						</p>
						{errors.map((error, i) => (
							<p key={i} className="text-red-500">
								- {error}
							</p>
						))}
					</>
				)}
			</Render>
		</>
	);
}

export default KudosDonate;
