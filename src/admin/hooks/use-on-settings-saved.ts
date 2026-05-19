import { useEffect, useRef } from '@wordpress/element';
import { addAction, removeAction } from '@wordpress/hooks';

export const useOnSettingsSaved = (callback: (settings: unknown) => void) => {
	const namespace = useRef(
		`kudos/use-on-settings-saved-${crypto.randomUUID()}`
	);
	useEffect(() => {
		const ns = namespace.current;
		addAction('kudos_settings_saved', ns, callback);
		return () => {
			removeAction('kudos_settings_saved', ns);
		};
	}, [callback]);
};
