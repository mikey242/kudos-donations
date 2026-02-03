import type { ReactNode } from 'react';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

interface SnackbarOptions {
	type?: 'snackbar' | 'default';
	icon?: ReactNode;
	isDismissible?: boolean;
	explicitDismiss?: boolean;
	onDismiss?: () => void;
}

/**
 * Wrapper hook for WordPress notices that correctly types the icon prop as ReactNode.
 *
 * The official @wordpress/notices types incorrectly define icon as `string | null`,
 * but the Snackbar component actually accepts ReactNode (including Icon components).
 *
 * @see https://wordpress.github.io/gutenberg/?path=/docs/components-snackbar--docs
 * @see https://github.com/WordPress/gutenberg/pull/49356
 */
export const useNotices = () => {
	const {
		createSuccessNotice,
		createErrorNotice,
		createWarningNotice,
		createInfoNotice,
	} = useDispatch(noticesStore);

	return {
		createSuccessNotice: (message: string, options?: SnackbarOptions) =>
			createSuccessNotice(
				message,
				options as Parameters<typeof createSuccessNotice>[1]
			),
		createErrorNotice: (message: string, options?: SnackbarOptions) =>
			createErrorNotice(
				message,
				options as Parameters<typeof createErrorNotice>[1]
			),
		createWarningNotice: (message: string, options?: SnackbarOptions) =>
			createWarningNotice(
				message,
				options as Parameters<typeof createWarningNotice>[1]
			),
		createInfoNotice: (message: string, options?: SnackbarOptions) =>
			createInfoNotice(
				message,
				options as Parameters<typeof createInfoNotice>[1]
			),
	};
};
