import { __ } from '@wordpress/i18n';
import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import {
	ChevronLeftIcon,
	ChevronRightIcon,
	LockClosedIcon,
} from '@heroicons/react/solid';
import Initial from './tabs/Initial';
import PaymentFrequency from './tabs/PaymentFrequency';
import Address from './tabs/Address';
import { Button } from '../../common/components/controls';
import Message from './tabs/Message';
import Summary from './tabs/Summary';
import { steps } from '../constants/form';
import {
	forwardRef,
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';
import { KudosLogo } from './KudosLogo';
import classNames from 'classnames';

const FormRouter = forwardRef(
	({ step, campaign, total, handlePrev, handleNext, submitForm }, ref) => {
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
					setTimeout(() => {
						setCurrentStep(step);
						target.classList.remove('translate-x-1', 'opacity-0');
						const newHeight =
							target.querySelector('form').offsetHeight;
						setHeight(newHeight + 'px');
						setTimeout(() => {
							setHeight('auto'); // This allows form to grow if validation message appear.
						}, 200);
					}, 200);
				}
			}
		}, [step]);

		return (
			<FormProvider {...methods}>
				<div className="relative">
					{isBusy && (
						<div className="absolute inset w-full h-full z-[2] flex justify-center items-center">
							<KudosLogo
								lineColor="#000"
								heartColor="#000"
								className="z-1 animate-spin w-6 h-6"
							/>
							<div className="absolute w-full h-full" />
						</div>
					)}
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
										<Initial
											title={campaign.initial_title}
											description={
												campaign.initial_description
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
											total={total}
										/>
									),
									2: (
										<PaymentFrequency
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
										<Address
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
										<Message
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
										<Summary
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
											'Prev',
											'kudos-donations'
										)}
										onClick={handlePrev}
									>
										<ChevronLeftIcon className="w-5 h-5" />
										<span className="mx-2">
											{__('Prev', 'kudos-donations')}
										</span>
									</Button>
								)}
								<Button
									type="submit"
									ariaLabel={__('Next', 'kudos-donations')}
									className="ml-auto text-base"
								>
									{steps[currentStep].name === 'Summary' ? (
										<>
											<LockClosedIcon className="w-5 h-5" />{' '}
											<span className="mx-2">
												{__(
													'Submit',
													'kudos-donations'
												)}
											</span>
										</>
									) : (
										<>
											<span className="mx-2">
												{__('Next', 'kudos-donations')}
											</span>
											<ChevronRightIcon className="w-5 h-5" />
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
