// eslint-disable-next-line import/default
import apiFetch from '@wordpress/api-fetch';
import { createPortal, useEffect, useState } from '@wordpress/element';
import React from 'react';
import { FormRouter } from './FormRouter';
import { KudosModal } from './KudosModal';
import Render from './Render';
import { useCampaignContext } from '../contexts/CampaignContext';
import { DonateButton } from './DonateButton';
import { KudosLogoFullScreenAnimated } from './KudosLogo';
import * as FrontControls from './controls';

interface KudosFormProps {
	displayAs: 'form' | 'button' | 'fslogo';
	label?: string;
	alignment?: 'left' | 'center' | 'right';
	previewMode?: boolean;
}

interface FormState {
	currentStep: number;
	formData: Record<string, any>;
}

export const KudosForm = ({
	displayAs,
	label,
	alignment,
	previewMode = false,
}: KudosFormProps) => {
	const { campaign, campaignErrors, isLoading } = useCampaignContext();
	const [timestamp, setTimestamp] = useState<number>(0);
	const [formError, setFormError] = useState<string | null>(null);
	const [formState, setFormState] = useState<FormState>({
		currentStep: 0,
		formData: {},
	});
	const [isModalOpen, setIsModalOpen] = useState<boolean>(false);
	const isForm = displayAs === 'form';
	const isModal = displayAs === 'button';
	const isFSLogo = displayAs === 'fslogo';

	// Add controls to kudos property for external access.
	window.kudos.FrontControls = FrontControls;

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
			currentStep: 0,
			formData: {},
		}));
	};

	async function submitForm(data: Record<string, any>): Promise<any> {
		if (previewMode) {
			return;
		}
		setFormError(null);
		const formData = new window.FormData();
		formData.append('timestamp', timestamp.toString());
		formData.append('campaign_id', campaign!.id.toString());
		formData.append(
			'return_url',
			String(
				campaign!.meta.use_custom_return_url
					? campaign!.meta.custom_return_url
					: window.location.href
			)
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
			body: new URLSearchParams(formData as any),
		})
			.then((result: any) => {
				if (result.success) {
					window.location.href = result.url;
				} else {
					setFormError(result.data.message);
				}
				return result;
			})
			.catch((error: any) => {
				setFormError(error.message);
				return error;
			});
	}

	const renderModal = () => {
		const portalContainer = document.getElementById('kudos-portal');
		if (portalContainer) {
			return createPortal(
				<Render
					themeColor={campaign?.meta?.theme_color}
					style={campaign?.meta?.custom_styles}
					errors={campaignErrors}
					className={previewMode && 'pointer-events-none'}
					alignment={alignment}
				>
					<KudosModal toggleModal={toggleModal} isOpen={isModalOpen}>
						{renderDonationForm()}
					</KudosModal>
				</Render>,
				portalContainer
			);
		}
		return (
			<KudosModal toggleModal={toggleModal} isOpen={isModalOpen}>
				{renderDonationForm()}
			</KudosModal>
		);
	};

	const renderDonationForm = () => (
		<>
			{formError && (
				<small className="text-center block font-normal mb-4 text-sm text-red-500">
					{formError}
				</small>
			)}
			{campaign && (
				<FormRouter
					step={formState?.currentStep}
					campaign={campaign}
					setFormState={setFormState}
					submitForm={submitForm}
				/>
			)}
		</>
	);

	if (isLoading) {
		return;
	}

	return (
		<>
			<Render
				themeColor={campaign?.meta?.theme_color}
				style={campaign?.meta?.custom_styles}
				errors={campaignErrors}
				className={previewMode && 'pointer-events-none'}
				alignment={alignment}
			>
				<>
					{isFSLogo && <KudosLogoFullScreenAnimated />}
					{isForm && renderDonationForm()}
					{isModal && (
						<>
							<DonateButton onClick={toggleModal}>
								{label}
							</DonateButton>
							{renderModal()}
						</>
					)}
				</>
			</Render>
		</>
	);
};
