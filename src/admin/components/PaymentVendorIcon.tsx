import React from 'react';

interface PaymentVendorIconProps {
	icon?: string;
	size?: number;
	style?: React.CSSProperties;
}

export const PaymentVendorIcon = ({
	icon,
	size = 25,
	style,
}: PaymentVendorIconProps): React.ReactNode =>
	icon ? (
		<img
			width={size}
			height={size}
			alt=""
			style={style}
			src={`data:image/svg+xml;utf8,${encodeURIComponent(icon)}`}
		/>
	) : null;
