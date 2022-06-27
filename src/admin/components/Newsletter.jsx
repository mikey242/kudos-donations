import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import { Button, TextControl } from '../../common/components/controls';
import MailchimpSubscribe from 'react-mailchimp-subscribe';
import { __ } from '@wordpress/i18n';

const formUrl =
	'https://media.us7.list-manage.com/subscribe/post-json?u=3239d6f13ed4f9a69d6610714&amp;id=d06b95e747';
const tagId = '6697111';

const Newsletter = () => {
	const methods = useForm();
	return (
		<>
			<FormProvider {...methods}>
				<MailchimpSubscribe
					url={formUrl}
					render={({ subscribe, status, message }) => (
						<div className="text-center">
							{status === 'success' ? (
								<h1 className="text-green-600">
									{__('Subscribed!', 'kudos-donations')}
								</h1>
							) : (
								<form
									onSubmit={methods.handleSubmit((data) => {
										subscribe({
											...data,
											tags: tagId,
										});
									})}
								>
									<h1>
										{__(
											'Signup for our newsletter',
											'kudos-donations'
										)}
									</h1>
									{status === 'sending' && (
										<div className="text-blue-500">
											sending...
										</div>
									)}
									{status === 'error' && (
										<div className="text-red-500">
											{message}
										</div>
									)}
									<TextControl
										validation={{
											required: __(
												'Your name is required',
												'kudos-donations'
											),
										}}
										placeholder={__(
											'Name',
											'kudos-donations'
										)}
										name="NAME"
									/>
									<TextControl
										type="email"
										name="EMAIL"
										validation={{
											required: __(
												'Your email is required',
												'kudos-donations'
											),
										}}
										placeholder={__(
											'Email',
											'kudos-donations'
										)}
									/>
									<div className="mt-8 flex justify-end relative">
										<Button type="submit" className="mt-3">
											Submit
										</Button>
									</div>
								</form>
							)}
						</div>
					)}
				/>
			</FormProvider>
		</>
	);
};

export { Newsletter };
