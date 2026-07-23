import React from 'react';

interface PaymentVendorIconProps {
	icon?: string;
	size?: number;
	style?: React.CSSProperties;
	title?: string;
}

export const PaymentVendorIcon = ({
	icon,
	size = 25,
	style,
	title = null,
}: PaymentVendorIconProps): React.ReactNode =>
	icon ? (
		<img
			width={size}
			height={size}
			alt=""
			style={style}
			title={title}
			src={`data:image/svg+xml;utf8,${encodeURIComponent(icon)}`}
		/>
	) : null;
