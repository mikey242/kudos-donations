import { createPortal, useEffect, useState } from '@wordpress/element';
import React from 'react';
import { FormRouter } from './FormRouter';
import { KudosModal } from './KudosModal';
import { useCampaignContext } from '../contexts';
import { DonateButton } from './DonateButton';
import { KudosLogoFullScreenAnimated, Render } from '../components';
import { applyFilters } from '@wordpress/hooks';
import { clsx } from 'clsx';

interface KudosFormProps {
	displayAs: 'form' | 'button' | 'fslogo';
	label?: string;
	alignment?: 'left' | 'center' | 'right';
	previewMode?: boolean;
}

export const KudosForm = ({
	displayAs,
	label,
	alignment,
	previewMode = false,
}: KudosFormProps) => {
	const { campaign, campaignErrors, isLoading } = useCampaignContext();
	const [timestamp] = useState<number>(() => Date.now());
	const [formError, setFormError] = useState<string | null>(null);
	const [currentStep, setCurrentStep] = useState<number>(0);
	const [isModalOpen, setIsModalOpen] = useState<boolean>(false);
	const isForm = displayAs === 'form';
	const isModal = displayAs === 'button';
	const isFSLogo = displayAs === 'fslogo';

	useEffect(() => {
		if (!isModalOpen) {
			resetForm();
		}
	}, [isModalOpen]);

	const toggleModal = () => {
		setIsModalOpen(!isModalOpen);
	};

	const resetForm = () => {
		setCurrentStep(0);
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
				campaign!.use_custom_return_url
					? campaign!.custom_return_url
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

		const root: string =
			(window as Window & { wpApiSettings?: { root?: string } })
				?.wpApiSettings?.root ?? '/wp-json/';
		const url = `${root.replace(/\/$/, '')}/kudos/v1/payment/create`;

		return fetch(url, {
			method: 'POST',
			body: new URLSearchParams(formData as any),
		})
			.then((res) => res.json())
			.then((result: any) => {
				if (result.success) {
					window.location.href = result.url;
				} else {
					setFormError(result.data?.message ?? result.message);
				}
				return result;
			})
			.catch((error: any) => {
				setFormError(error.message);
				return error;
			});
	}

	const showLogo: boolean = !applyFilters(
		'kudosHideBranding',
		false
	) as boolean;

	const renderModal = () => {
		const portalContainer = document.getElementById('kudos-portal');
		if (portalContainer) {
			return createPortal(
				<Render
					themeColor={campaign?.theme_color}
					style={campaign?.custom_styles}
					errors={campaignErrors}
					className={previewMode ? 'pointer-events-none' : undefined}
					alignment={alignment}
				>
					<KudosModal
						showLogo={showLogo}
						toggleModal={toggleModal}
						isOpen={isModalOpen}
					>
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
		<div id="kudos-donation-form" className="w-full">
			{formError && (
				<small className="text-center block font-normal mb-4 text-sm text-red-500">
					{formError}
				</small>
			)}
			{campaign && (
				<FormRouter
					step={currentStep}
					campaign={campaign}
					onStepChange={setCurrentStep}
					submitForm={submitForm}
				/>
			)}
		</div>
	);

	return (
		<Render
			themeColor={campaign?.theme_color}
			style={campaign?.custom_styles}
			errors={campaignErrors}
			className={clsx(previewMode && 'pointer-events-none')}
			previewMode={previewMode}
			alignment={alignment}
			isContentReady={!isLoading}
		>
			<div id="kudos-content">
				{isFSLogo && <KudosLogoFullScreenAnimated />}
				{isForm && renderDonationForm()}
				{isModal && (
					<>
						<DonateButton showLogo={showLogo} onClick={toggleModal}>
							{label}
						</DonateButton>
						{renderModal()}
					</>
				)}
			</div>
		</Render>
	);
};
