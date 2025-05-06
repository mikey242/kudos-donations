import { __ } from '@wordpress/i18n';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import {
	InitialTab,
	FrequencyTab,
	AddressTab,
	MessageTab,
	SummaryTab,
} from './tabs';
import { Button } from './controls';
import { useLayoutEffect, useMemo, useRef, useState } from '@wordpress/element';
import { clsx } from 'clsx';
import {
	ChevronLeftIcon,
	ChevronRightIcon,
	LockClosedIcon,
} from '@heroicons/react/24/outline';
import { applyFilters } from '@wordpress/hooks';
import BaseTab from './tabs/BaseTab';

const checkRequirements = (tabs, data, target) => {
	const reqs = tabs[target]?.requirements;
	if (!reqs) {
		return true;
	}

	return Object.entries(reqs).every(([key, val]) => data[key] === val);
};

export const FormRouter = ({ step, campaign, submitForm, setFormState }) => {
	const [height, setHeight] = useState('');
	const [currentStep, setCurrentStep] = useState(step);
	const prevStep = useRef(step); // Ref to keep track of the previous step
	const [isBusy, setIsBusy] = useState(false);
	const elementRef = useRef(null);
	const firstUpdate = useRef(true);
	const methods = useForm({
		defaultValues: {
			recurring: false,
			business_name: '',
			city: '',
			country: '',
			postcode: '',
			street: '',
			message: '',
		},
	});

	const Tabs = useMemo(
		() =>
			applyFilters(
				'kudosFormTabs',

				[
					{
						name: 'Initial',
						element: InitialTab,
					},
					{
						name: 'Recurring',
						element: FrequencyTab,
						requirements: {
							recurring: true,
						},
					},
					{
						name: 'Address',
						element: AddressTab,
						requirements: {
							address_enabled: true,
						},
					},
					{
						name: 'Message',
						element: MessageTab,
						requirements: {
							message_enabled: true,
						},
					},
					{
						name: 'Summary',
						element: SummaryTab,
					},
				],
				campaign,
				BaseTab
			),
		[campaign]
	);

	const currentTab = Tabs[currentStep];
	const CurrentTab = currentTab.element;

	const handlePrev = () => {
		if (currentStep === 0) {
			return;
		}
		let prev = currentStep - 1;
		const state = { ...methods.getValues(), ...campaign.meta };

		// Find next available step.
		while (!checkRequirements(Tabs, state, prev) && prev >= 0) {
			prev--;
		}
		setFormState((prevState) => ({
			...prevState,
			currentStep: prev,
		}));
	};

	const handleNext = (data) => {
		const state = { ...data, ...campaign.meta };
		let nextStep = currentStep + 1;

		// Find next available step.
		while (
			!checkRequirements(Tabs, state, nextStep) &&
			nextStep <= Tabs.length
		) {
			nextStep++;
		}

		setFormState((prev) => ({
			...prev,
			formData: { ...prev?.formData, ...data },
			currentStep: nextStep,
		}));
	};

	const onSubmit = (data) => {
		if (currentStep < Tabs.length - 1) {
			return handleNext(data);
		}

		setIsBusy(true);
		submitForm(data).then((result) => {
			if (!result?.success) {
				setIsBusy(false);
			}
		});
	};

	useLayoutEffect(() => {
		// Skip on first render
		if (firstUpdate.current) {
			firstUpdate.current = false;
			return;
		}

		// Only proceed if the step has changed
		if (prevStep.current !== step) {
			if (!elementRef.current) {
				return;
			}

			const target = elementRef.current;
			target.classList.add('translate-x-1', 'opacity-0');

			const oldHeight = target.querySelector('form').offsetHeight;
			setHeight(oldHeight);
			const resizeObserver = new ResizeObserver(() => {
				const newHeight = target.querySelector('form').offsetHeight;
				setHeight(newHeight);

				const timeout = setTimeout(() => {
					setHeight('auto'); // Allow form to grow if validation message appears.
					setCurrentStep(step);
					target.classList.remove(
						'translate-x-1',
						'opacity-0',
						'section-' + Tabs[prevStep.current]?.name?.toLowerCase()
					);
					target.classList.add(
						'section-' + Tabs[step]?.name?.toLowerCase()
					);
					prevStep.current = step; // Update the previous step to the current step
				}, 200);

				return () => clearTimeout(timeout);
			});

			resizeObserver.observe(target.querySelector('form'));

			return () => resizeObserver.disconnect();
		}
	}, [Tabs, step]); // Only rerun when `step` changes

	return (
		<FormProvider {...methods}>
			<div
				ref={elementRef}
				id="form-container"
				className={clsx(
					isBusy && 'opacity-50',
					'transition-all duration-200'
				)}
				style={{ height: height + 'px' }}
			>
				<form id="form" onSubmit={methods.handleSubmit(onSubmit)}>
					<CurrentTab campaign={campaign} />
					<div
						id="form-buttons"
						className="mt-8 flex justify-between relative"
					>
						{currentStep > 0 && (
							<Button
								type="button"
								className="text-base"
								ariaLabel={__('Back', 'kudos-donations')}
								onClick={handlePrev}
								icon={
									<ChevronLeftIcon className="mr-2 w-5 h-5" />
								}
							>
								<p>{__('Back', 'kudos-donations')}</p>
							</Button>
						)}
						<Button
							type="submit"
							ariaLabel={__('Next', 'kudos-donations')}
							className="ml-auto text-base"
							isBusy={isBusy}
							icon={
								currentTab.name === 'Summary' && (
									<LockClosedIcon className="mr-2 w-5 h-5" />
								)
							}
						>
							{currentTab.name === 'Summary' ? (
								<p>{__('Submit', 'kudos-donations')}</p>
							) : (
								<>
									<p>{__('Next', 'kudos-donations')}</p>
									<ChevronRightIcon className="ml-2 w-5 h-5" />
								</>
							)}
						</Button>
					</div>
				</form>
			</div>
		</FormProvider>
	);
};
