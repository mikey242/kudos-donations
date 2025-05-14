import { __ } from '@wordpress/i18n';
import React from 'react';
import { FormProvider, useForm, UseFormReturn } from 'react-hook-form';
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
import { Campaign } from '../contexts/CampaignContext';

interface TabDefinition {
	name: string;
	element: React.ComponentType<{ campaign: Campaign }>;
	requirements?: Record<string, any>;
}
interface Tab {
	name: string;
	element: React.ComponentType<{ campaign: Campaign }>;
	requirements?: Record<string, any>;
}

interface FormData {
	recurring: boolean;
	business_name: string;
	city: string;
	country: string;
	postcode: string;
	street: string;
	message: string;
	[key: string]: any;
}
interface FormRouterProps {
	step: number;
	campaign: Campaign;
	submitForm: (data: FormData) => Promise<any>;
	setFormState: React.Dispatch<
		React.SetStateAction<{
			currentStep: number;
			formData: Record<string, any>;
		}>
	>;
}

const checkRequirements = (
	tabs: Tab[],
	data: Record<string, any>,
	target: number
): boolean => {
	const reqs = tabs[target]?.requirements;
	if (!reqs) {
		return true;
	}

	return Object.entries(reqs).every(([key, val]) => data[key] === val);
};

export const FormRouter = ({
	step,
	campaign,
	submitForm,
	setFormState,
}: FormRouterProps) => {
	const [height, setHeight] = useState<string>('');
	const [currentStep, setCurrentStep] = useState<number>(step);
	const prevStep = useRef<number>(step); // Ref to keep track of the previous step
	const [isBusy, setIsBusy] = useState<boolean>(false);
	const elementRef = useRef<HTMLDivElement>(null);
	const firstUpdate = useRef<boolean>(true);
	const timeoutRef = useRef<ReturnType<typeof setTimeout | null>>(null);
	const methods: UseFormReturn<FormData> = useForm<FormData>({
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

	const Tabs = useMemo<TabDefinition[]>(
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
			) as TabDefinition[],
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

	const handleNext = (data: FormData) => {
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

	const onSubmit = (data: FormData) => {
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
			setHeight(oldHeight.toString());
			const resizeObserver = new ResizeObserver(() => {
				const newHeight = target.querySelector('form').offsetHeight;
				setHeight(newHeight.toString());

				timeoutRef.current = setTimeout(() => {
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
			});

			resizeObserver.observe(target.querySelector('form'));

			return () => {
				resizeObserver.disconnect();
				clearTimeout(timeoutRef.current);
			};
		}
	}, [Tabs, step]); // Only rerun when `step` changes

	return (
		<FormProvider {...methods}>
			<div
				ref={elementRef}
				id="form-container"
				className={clsx(
					isBusy && 'opacity-50',
					'w-full transition-all duration-200'
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
