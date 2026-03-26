export const confirmDelete = (message: string, onConfirm: () => void): void => {
	// eslint-disable-next-line no-alert
	if (window.confirm(message)) {
		onConfirm();
	}
};

export const isValidUrl = (value: string): boolean => {
	let url: URL;
	try {
		url = new URL(value);
	} catch (_) {
		return false;
	}
	return url.protocol === 'http:' || url.protocol === 'https:';
};

// @see https://github.com/orgs/react-hook-form/discussions/1991#discussioncomment-31308
export const dirtyValues = <T extends Record<string, unknown>>(
	dirtyFields: unknown,
	allValues: T
): T => {
	if (dirtyFields === true || Array.isArray(dirtyFields)) {
		return allValues;
	}
	return Object.fromEntries(
		Object.entries(dirtyFields)
			.map(([key, value]) => {
				if (
					value &&
					typeof value === 'object' &&
					!Array.isArray(value)
				) {
					const nestedDirty = dirtyValues(
						value,
						allValues[key] as Record<string, unknown>
					);
					return nestedDirty !== undefined
						? [key, allValues[key]]
						: undefined;
				}
				return value === true ? [key, allValues[key]] : undefined;
			})
			.filter((entry) => entry !== undefined)
	) as T;
};
