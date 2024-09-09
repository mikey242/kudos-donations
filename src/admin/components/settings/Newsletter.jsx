import React from 'react';
// eslint-disable-next-line import/default
import MailchimpSubscribe from 'react-mailchimp-subscribe';
import { __ } from '@wordpress/i18n';
import { Button, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

const formUrl =
	'https://media.us7.list-manage.com/subscribe/post-json?u=3239d6f13ed4f9a69d6610714&amp;id=d06b95e747';
const tagId = '6697111';

const Newsletter = () => {
	const [name, setName] = useState('');
	const [email, setEmail] = useState('');

	return (
		<div className="kudos-intro-guide-text">
			<MailchimpSubscribe
				url={formUrl}
				render={({ subscribe, status, message }) => (
					<div>
						{status === 'success' ? (
							<h1>{__('Subscribed!', 'kudos-donations')}</h1>
						) : (
							<form
								onSubmit={(data) =>
									subscribe({
										...data,
										tags: tagId,
									})
								}
							>
								{status === 'sending' && <div>sending...</div>}
								{status === 'error' && <div>{message}</div>}
								<TextControl
									placeholder={__('Name', 'kudos-donations')}
									value={name}
									name="NAME"
									onChange={setName}
								/>
								<TextControl
									type="email"
									value={email}
									name="EMAIL"
									onChange={setEmail}
									placeholder={__('Email', 'kudos-donations')}
								/>
								<Button variant="primary" type="submit">
									{__('Subscribe', 'kudos-donations')}
								</Button>
							</form>
						)}
					</div>
				)}
			/>
		</div>
	);
};

export { Newsletter };
