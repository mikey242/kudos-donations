import apiFetch from '@wordpress/api-fetch';
import { useEffect, useRef, useState } from '@wordpress/element';
import React from 'react';
import PropTypes from 'prop-types';
import { getStyle } from '../../common/helpers/util';
import { KudosButton } from './KudosButton';
import KudosModal from './KudosModal';
import FormRouter from './FormRouter';
import { checkRequirements } from '../../common/helpers/form';
import { anim } from '../../common/helpers/animate';
import KudosRender from './KudosRender';
import {
	fetchCampaigns,
	fetchCampaignTransactions,
} from '../../common/helpers/fetch';

const stylesheet = document.getElementById('kudos-donations-public-css');

KudosDonate.propTypes = {
	buttonLabel: PropTypes.string,
	root: PropTypes.object,
};

function KudosDonate({ buttonLabel, campaignId, root }) {
	const [campaign, setCampaign] = useState();
	const [total, setTotal] = useState(0);
	const [timestamp, setTimestamp] = useState();
	const [ready, setReady] = useState(false);
	const [errors, setErrors] = useState([]);
	const [formState, setFormState] = useState();
	const [isBusy, setIsBusy] = useState(false);

	const [modalOpen, setModalOpen] = useState(false);
	const modal = useRef(null);

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
		const target = modal.current;
		let step = currentStep - 1;
		const state = { ...formState?.formData, ...campaign };

		// Find next available step.
		while (!checkRequirements(state, step) && step >= 1) {
			step--;
		}

		anim(
			target,
			() => {
				setFormState((prev) => ({
					...prev,
					currentStep: step,
				}));
			},
			['translate-x-1']
		);
	};

	const handleNext = (data, step) => {
		const state = { ...data, ...campaign };
		const target = modal.current;

		// Find next available step.
		while (!checkRequirements(state, step) && step <= 10) {
			step++;
		}

		anim(
			target,
			() => {
				setFormState((prev) => ({
					...prev,
					formData: { ...prev?.formData, ...data },
					currentStep: step,
				}));
			},
			['-translate-x-1']
		);
	};

	const handleKeyPress = (e) => {
		if (e.key === 'Escape' || e.keyCode === 27) toggleModal();
	};

	const submitForm = (data) => {
		setErrors([]);
		setIsBusy(true);
		const formData = new FormData();
		formData.append('timestamp', timestamp);
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

		apiFetch({
			path: '/kudos/v1/payment/create',
			headers: new Headers({
				'Content-Type': 'multipart/tabs-data',
			}),
			method: 'POST',
			body: new URLSearchParams(formData),
		}).then((result) => {
			if (result.success) {
				window.location.href = result.data;
			} else {
				setIsBusy(false);
				setErrors([...errors, result.data.message]);
			}
		});
	};

	const getCampaign = () => {
		fetchCampaigns(campaignId).then((response) => {
			setCampaign(response?.meta);
			setTimestamp(Date.now());
			setReady(true);
		});
	};

	useEffect(() => {
		getCampaign();
		fetchCampaignTransactions(campaignId).then((transactions) => {
			setTotal(
				transactions.reduce((n, { value }) => n + parseInt(value), 0)
			);
		});
	}, []);

	useEffect(() => {
		if (modalOpen) {
			document.addEventListener('keydown', handleKeyPress, false);
		}
		return () =>
			document.removeEventListener('keydown', handleKeyPress, false);
	}, [modalOpen]);

	return (
		<>
			{ready && (
				<KudosRender
					themeColor={campaign?.theme_color}
					stylesheet={stylesheet.href}
				>
					<KudosButton onClick={toggleModal}>
						{buttonLabel}
					</KudosButton>
					<KudosModal
						toggle={toggleModal}
						root={root}
						ref={modal}
						isBusy={isBusy}
						isOpen={modalOpen}
					>
						<>
							{errors.length > 0 &&
								errors.map((e, i) => (
									<small
										key={i}
										className="text-center block font-normal mb-4 text-sm text-red-500"
									>
										{e}
									</small>
								))}
							<FormRouter
								step={formState?.currentStep ?? 1}
								campaign={campaign}
								total={total}
								handleNext={handleNext}
								handlePrev={handlePrev}
								submitForm={submitForm}
							/>
						</>
					</KudosModal>
				</KudosRender>
			)}
		</>
	);
}

export default KudosDonate;
