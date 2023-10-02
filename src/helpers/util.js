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

export function isValidUrl(string) {
	let url;

	try {
		url = new URL(string);
	} catch (_) {
		return false;
	}

	return url.protocol === 'http:' || url.protocol === 'https:';
}
