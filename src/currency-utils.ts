export const getCurrencySymbol = (currency: string): string =>
	window.kudos?.currencies?.[currency] ?? currency;
