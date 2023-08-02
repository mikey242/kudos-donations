import { __ } from '@wordpress/i18n';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import InitialTab from './InitialTab';
import FrequencyTab from './FrequencyTab';
import AddressTab from './AddressTab';
import { Button } from '../controls';
import MessageTab from './MessageTab';
import SummaryTab from './SummaryTab';
import { steps } from '../../constants/form';
import {
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';
import classNames from 'classnames';
import {
	ChevronLeftIcon,
	ChevronRightIcon,
	LockClosedIcon,
} from '@heroicons/react/24/outline';

const FormRouter =	({ step, campaign, handlePrev, handleNext, submitForm }) => {
		const [height, setHeight] = useState('');
		const [currentStep, setCurrentStep] = useState(1);
		const [isBusy, setIsBusy] = useState(false);
		const elementRef = useRef(null);
		const methods = useForm({
			defaultValues: {
				recurring: false,
			},
		});
		const firstUpdate = useRef(true);

		const onSubmit = (data) => {
			if (step < 5) return handleNext(data, step + 1);
			setIsBusy(true);
			submitForm(data).then((result) => {
				if (!result.success) {
					setIsBusy(false);
				}
			});
		};

		useLayoutEffect(() => {
			if (firstUpdate.current) {
				firstUpdate.current = false;
			} else {
				if (!elementRef.current ) return;
				const target = elementRef.current;
				target.classList.add('translate-x-1', 'opacity-0');
				const oldHeight = target.querySelector('form').offsetHeight;
				setHeight(oldHeight);
				const resizeObserver = new ResizeObserver(() => {
					const newHeight = target.querySelector('form').offsetHeight;
					setHeight(newHeight);
					const timeout = setTimeout(() => {
						setHeight('auto'); // This allows form to grow if validation message appear.
						setCurrentStep(step);
						target.classList.remove('translate-x-1', 'opacity-0');
					}, 200);
					return () => clearTimeout(timeout);
				})
				resizeObserver.observe(target.querySelector('form'))
				return () => resizeObserver.disconnect(); // clean up
			}
		}, [step])

		return (
			<FormProvider {...methods}>
				<div
					ref={elementRef}
					className={classNames(
						isBusy && 'opacity-50',
						'transition-all duration-200'
					)}
					style={{ height: height  + 'px' }}
				>
					<form onSubmit={methods.handleSubmit(onSubmit)}>
						{
							{
								1: (
									<InitialTab
										title={campaign.initial_title}
										description={
											campaign.initial_description
										}
										minimumDonation={
											campaign.minimum_donation
										}
										donationType={campaign.donation_type}
										amountType={campaign.amount_type}
										fixedAmounts={campaign.fixed_amounts}
										showGoal={campaign.show_goal}
										goal={campaign.goal}
										total={campaign.total}
									/>
								),
								2: (
									<FrequencyTab
										title={
											campaign.recurring_title
										}
										description={
											campaign.recurring_description
										}
									/>
								),
								3: (
									<AddressTab
										required={campaign.address_required}
										title={
											campaign.address_title
										}
										description={
											campaign.address_description
										}
									/>
								),
								4: (
									<MessageTab
										title={
											campaign.message_title
										}
										description={
											campaign.message_description
										}
									/>
								),
								5: (
									<SummaryTab
										title={
											campaign.payment_title
										}
										description={
											campaign.payment_description
										}
										privacyLink={campaign.privacy_link}
										termsLink={campaign.terms_link}
									/>
								),
							}[currentStep]
						}
						<div className="kudos-modal-buttons mt-8 flex justify-between relative">
							{currentStep > 1 && (
								<Button
									type="button"
									className="text-base"
									ariaLabel={__('Back', 'kudos-donations')}
									onClick={handlePrev}
									icon={
										<ChevronLeftIcon className="mr-2 w-5 h-5" />
									}
								>
									{__('Back', 'kudos-donations')}
								</Button>
							)}
							<Button
								type="submit"
								ariaLabel={__('Next', 'kudos-donations')}
								className="ml-auto text-base"
								isBusy={isBusy}
								icon={
									steps[currentStep].name === 'Summary' && (
										<LockClosedIcon className="mr-2 w-5 h-5" />
									)
								}
							>
								{steps[currentStep].name === 'Summary' ? (
									__('Submit', 'kudos-donations')
								) : (
									<>
										{__('Next', 'kudos-donations')}
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
export default FormRouter;
