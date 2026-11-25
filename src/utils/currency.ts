export const getCurrencySymbol = (currency: string): string =>
	window.kudos?.currencies?.[currency] ?? currency;

export const formatAmount = (
	value: number | string,
	currency: string
): string => `${getCurrencySymbol(currency)}${value}`;
