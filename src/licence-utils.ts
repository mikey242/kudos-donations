import apiFetch from '@wordpress/api-fetch';
import type { LicenceStatus, LicenceStatusString } from './types/licence';

export const isLicenceActive = (
	status: LicenceStatus | Record<string, never>
): boolean => {
	if (!status || !('valid' in status) || !status.valid) {
		return false;
	}
	if (status.expires_at) {
		return new Date(status.expires_at) > new Date();
	}
	return true;
};

export const getLicenceStatus = async (): Promise<LicenceStatusString> => {
	const settings = await apiFetch<{
		_kudos_licence_status: LicenceStatus | Record<string, never>;
	}>({ path: '/wp/v2/settings' });
	const status = settings._kudos_licence_status;
	if (!status || !('valid' in status) || !status.valid) {
		return 'not-set';
	}
	if (status.expires_at) {
		return new Date(status.expires_at) > new Date() ? 'active' : 'expired';
	}
	return 'active';
};