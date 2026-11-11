import { useEffect, useState } from '@wordpress/element';
import { queryEntities } from '../utils';

/**
 * Whether the site has received at least one paid live (not test) donation.
 *
 * @param enabled Whether to check; when false no request is made and the result is false.
 * @return Null while checking.
 */
export const useHasLiveDonation = (enabled: boolean): boolean | null => {
	const [hasLiveDonation, setHasLiveDonation] = useState<boolean | null>(
		enabled ? null : false
	);

	useEffect(() => {
		if (!enabled) {
			setHasLiveDonation(false);
			return;
		}
		queryEntities('transaction', {
			per_page: 1,
			where: { status: 'paid', mode: 'live' },
		})
			.then((response) => setHasLiveDonation(response.total > 0))
			.catch(() => setHasLiveDonation(false));
	}, [enabled]);

	return hasLiveDonation;
};
