import { __ } from '@wordpress/i18n';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import {
	ChevronLeftIcon,
	ChevronRightIcon,
	LockClosedIcon,
} from '@heroicons/react/solid';
import InitialTab from './InitialTab';
import FrequencyTab from './FrequencyTab';
import AddressTab from './AddressTab';
import { Button } from '../../common/components/controls';
import MessageTab from './MessageTab';
import SummaryTab from './SummaryTab';
import { steps } from '../constants/form';
import {
	forwardRef,
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';
import classNames from 'classnames';

const FormRouter = forwardRef(
	({ step, campaign, handlePrev, handleNext, submitForm }, ref) => {
		const [height, setHeight] = useState('');
		const [currentStep, setCurrentStep] = useState(1);
		const [isBusy, setIsBusy] = useState(false);
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
				const target = ref?.current;
				if (target) {
					target.classList.add('translate-x-1', 'opacity-0');
					const oldHeight = target.querySelector('form').offsetHeight;
					setHeight(oldHeight);
					const timeout = setTimeout(() => {
						setCurrentStep(step);
						target.classList.remove('translate-x-1', 'opacity-0');
						const newHeight =
							target.querySelector('form').offsetHeight;
						setHeight(newHeight + 'px');
						setTimeout(() => {
							setHeight('auto'); // This allows form to grow if validation message appear.
						}, 200);
					}, 200);
					return () => clearTimeout(timeout);
				}
			}
		}, [step]);

		return (
			<FormProvider {...methods}>
				<div className="relative">
					<div
						ref={ref}
						className={classNames(
							isBusy && 'opacity-50',
							'transition-all duration-200'
						)}
						style={{ height }}
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
											donationType={
												campaign.donation_type
											}
											amountType={campaign.amount_type}
											fixedAmounts={
												campaign.fixed_amounts
											}
											showGoal={campaign.show_goal}
											goal={campaign.goal}
											total={campaign.total}
										/>
									),
									2: (
										<FrequencyTab
											title={
												campaign.recurring_title ??
												__(
													'Subscription',
													'kudos-donations'
												)
											}
											description={
												campaign.recurring_description ??
												__(
													'How often would you like to donate?',
													'kudos-donations'
												)
											}
										/>
									),
									3: (
										<AddressTab
											required={campaign.address_required}
											title={
												campaign.address_title ??
												__('Address', 'kudos-donations')
											}
											description={
												campaign.address_description ??
												__(
													'Please fill in your address',
													'kudos-donations'
												)
											}
										/>
									),
									4: (
										<MessageTab
											title={
												campaign.message_title ??
												__('Message', 'kudos-donations')
											}
											description={
												campaign.message_description ??
												__(
													'Leave a message (optional).',
													'kudos-donations'
												)
											}
										/>
									),
									5: (
										<SummaryTab
											title={
												campaign.summary_title ??
												__('Payment', 'kudos-donations')
											}
											description={
												campaign.summary_description ??
												__(
													'By clicking donate you agree to the following payment:',
													'kudos-donations'
												)
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
										ariaLabel={__(
											'Back',
											'kudos-donations'
										)}
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
										steps[currentStep].name ===
											'Summary' && (
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
				</div>
			</FormProvider>
		);
	}
);
export default FormRouter;
