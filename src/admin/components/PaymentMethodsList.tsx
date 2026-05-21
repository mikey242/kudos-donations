import { __ } from '@wordpress/i18n';
import React from 'react';
import type { VendorPaymentMethod } from '../../types/settings';
import { PaymentIcon } from './PaymentIcon';

interface PaymentMethodsListProps {
	methods?: VendorPaymentMethod[];
}

export const PaymentMethodsList = ({
	methods = [],
}: PaymentMethodsListProps) => {
	if (methods.length === 0) {
		return <i>{__('No payment methods found.', 'kudos-donations')}</i>;
	}

	return (
		<div style={{ display: 'flex', flexWrap: 'wrap', gap: '10px' }}>
			{methods.map((method) => (
				<div
					key={method.id}
					style={{
						display: 'flex',
						flexDirection: 'column',
						alignItems: 'center',
						gap: 10,
					}}
				>
					<PaymentIcon id={method.id} />
					<small>{method.label}</small>
				</div>
			))}
		</div>
	);
};
