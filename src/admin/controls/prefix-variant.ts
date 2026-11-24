import type { ReactNode } from 'react';

/**
 * The InputControlPrefixWrapper variant for a prefix: text such as a currency symbol gets
 * the default padding, while an element (an icon) is sized and centred as one.
 *
 * @param prefix The prefix passed to the control.
 */
export const prefixVariant = (prefix: ReactNode): 'default' | 'icon' =>
	typeof prefix === 'string' || typeof prefix === 'number'
		? 'default'
		: 'icon';
