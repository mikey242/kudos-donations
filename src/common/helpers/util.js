export function camelize(str) {
	return str
		.replace(/(^\w|[A-Z]|\b\w)/g, function (word, index) {
			return index === 0 ? word.toLowerCase() : word.toUpperCase();
		})
		.replace(/\s+/g, '');
}

export function getQueryVar(key, fallback = null) {
	const searchParams = new URLSearchParams(window.location.search);
	if (searchParams.has(key)) {
		return searchParams.get(key);
	}
	return fallback;
}

export function updateQueryParameter(key, value) {
	if ('URLSearchParams' in window) {
		const searchParams = new URLSearchParams(window.location.search);
		searchParams.set(key, value);
		const newRelativePathQuery =
			window.location.pathname + '?' + searchParams.toString();
		history.replaceState(null, '', newRelativePathQuery);
	}
}

export function removeQueryParameters(keys) {
	keys.forEach((key) => {
		if ('URLSearchParams' in window) {
			const searchParams = new URLSearchParams(window.location.search);
			searchParams.delete(key);
			const newRelativePathQuery =
				window.location.pathname + '?' + searchParams.toString();
			history.replaceState(null, '', newRelativePathQuery);
		}
	});
}

export function getStyle(style) {
	return getComputedStyle(document.documentElement).getPropertyValue(style);
}

export function setAttributes(el, attrs) {
	Object.entries(attrs).forEach(([key, value]) =>
		el.setAttribute(key, value)
	);
}

export function isVisible(el) {
	// Check if element visible
	if (el.offsetParent === null) return false;

	// Check if in viewport
	const rect = el.getBoundingClientRect();
	return (
		rect.top >= 0 &&
		rect.left >= 0 &&
		rect.bottom <=
			(window.innerHeight || document.documentElement.clientHeight) &&
		rect.right <=
			(window.innerWidth || document.documentElement.clientWidth)
	);
}

export function isValidUrl(string) {
	let url;

	try {
		url = new URL(string);
	} catch (_) {
		return false;
	}

	return url.protocol === 'http:' || url.protocol === 'https:';
}

export function getTotal(campaignId, transactions) {
	const filtered = transactions.filter((transaction) => {
		return parseInt(transaction.campaign_id) === campaignId;
	});
	if (filtered.length) {
		return filtered.reduce((a, b) => a + parseInt(b.value), 0);
	}
	return 0;
}
