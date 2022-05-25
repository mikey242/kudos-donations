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
import { forwardRef, useEffect, useState } from '@wordpress/element';

const FormRouter = forwardRef(
	({ step, campaign, total, handlePrev, handleNext, submitForm }, ref) => {
		const [height, setHeight] = useState('');
		const methods = useForm({
			defaultValues: {
				recurring: false,
			},
		});

		const onSubmit = (data) => {
			if (step < 5) return handleNext(data, step + 1);
			return submitForm(data);
		};

		useEffect(() => {
			const target = ref?.current;
			if (target) {
				const newHeight = target.querySelector('form').offsetHeight;
				setHeight(newHeight + 'px');
			}
		}, [step]);

		return (
			<FormProvider {...methods}>
				<div
					ref={ref}
					className="transition-all duration-200"
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
										donationType={campaign.donation_type}
										amountType={campaign.amount_type}
										fixedAmounts={campaign.fixed_amounts}
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
							}[step]
						}
						<div className="kudos-modal-buttons mt-8 flex justify-between relative">
							{step > 1 && (
								<Button
									type="button"
									className="text-base"
									ariaLabel={__('Prev', 'kudos-donations')}
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
								{steps[step].name === 'Summary' ? (
									<>
										<LockClosedIcon className="w-5 h-5" />{' '}
										<span className="mx-2">
											{__('Submit', 'kudos-donations')}
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
			</FormProvider>
		);
	}
);
export default FormRouter;
