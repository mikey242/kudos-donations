export const getCurrencySymbol = (currency: string): string =>
	window.kudos?.currencies?.[currency] ?? currency;

const formatters = new Map<string, Intl.NumberFormat>();

const getFormatter = (currency: string, wholeNumber: boolean) => {
	const key = `${currency}:${wholeNumber}`;
	let formatter = formatters.get(key);
	if (!formatter) {
		formatter = new Intl.NumberFormat(
			document.documentElement.lang || undefined,
			{
				style: 'currency',
				currency,
				...(wholeNumber && { minimumFractionDigits: 0 }),
			}
		);
		formatters.set(key, formatter);
	}
	return formatter;
};

// Formats an amount in the page's locale, e.g. €2,000.00 (en) or € 2.000,00 (nl).
// Decimals are always shown so right-aligned amounts line up, unless
// trimWholeNumbers is set: then round amounts drop them (€2,000).
export const formatAmount = (
	value: number | string,
	currency: string,
	{ trimWholeNumbers = false } = {}
): string => {
	const amount = Number(value);
	try {
		return getFormatter(
			currency,
			trimWholeNumbers && Number.isInteger(amount)
		).format(amount);
	} catch {
		// Unknown currency code.
		return `${getCurrencySymbol(currency)}${value}`;
	}
};
