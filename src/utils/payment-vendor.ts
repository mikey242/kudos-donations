export type PaymentVendor = { slug: string; label: string; icon: string };

export const getPaymentVendors = (): PaymentVendor[] =>
	window.kudos?.admin?.payment_vendors ?? [];

export const getPaymentVendor = (slug: string): PaymentVendor | undefined =>
	getPaymentVendors().find((v) => v.slug === slug);
