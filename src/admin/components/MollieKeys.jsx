import React from 'react';
import { FormProvider, useForm } from 'react-hook-form';
import { Button, TextControl } from '../../common/components/controls';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

const MollieKeys = ({ checkApiKey }) => {
	const [isApiSaving, setIsApiSaving] = useState(false);
	const [apiMessage, setApiResponse] = useState(null);

	const methods = useForm();

	const submitMollie = (data) => {
		setIsApiSaving(true);
		checkApiKey({
			keys: data.keys,
		}).then((response) => {
			if (!response.success) {
				console.log(response);
				setApiResponse(response.data.message);
			}
			setIsApiSaving(false);
		});
	};

	return (
		<>
			{apiMessage && (
				<div className="my-2 text-sm text-white rounded-md p-3 bg-red-500">
					{apiMessage}
				</div>
			)}
			<FormProvider {...methods}>
				<form onSubmit={methods.handleSubmit(submitMollie)}>
					<TextControl
						name="keys.live_key"
						disabled={isApiSaving}
						placeholder={__('Live key', 'kudos-donations')}
					/>
					<TextControl
						name="keys.test_key"
						disabled={isApiSaving}
						placeholder={__('Test key', 'kudos-donations')}
					/>
					<div className="mt-3 flex justify-end relative">
						<Button
							isSmall
							type="submit"
							className="w-full"
							isDisabled={isApiSaving}
						>
							{__('Connect', 'kudos-donations')}
						</Button>
					</div>
				</form>
			</FormProvider>
		</>
	);
};

export { MollieKeys };
