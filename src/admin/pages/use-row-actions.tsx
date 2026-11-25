import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	payment as paymentIcon,
	pencil as pencilIcon,
	trash as trashIcon,
} from '@wordpress/icons';
import type { RowAction } from '../components';
import { useEntitiesContext, useSettingsContext } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import type { BaseEntity } from '../../types/entity';
import { confirmDelete } from '../../utils';

type TransactionLink = 'donor_id' | 'campaign_id' | 'subscription_id';

/**
 * Row actions shared by the entity tables. Spread one to override a
 * property, e.g. `{ ...actions.viewTransactions('donor_id'), isDisabled }`.
 */
export const useRowActions = <T extends BaseEntity>() => {
	const { setParams, updateParams } = useAdminQueryParams();
	const { handleDelete } = useEntitiesContext<T>();
	const { settings } = useSettingsContext();
	const isDebug = Boolean(settings._kudos_debug_mode);

	return useMemo(
		() => ({
			edit: (label: string): RowAction<T> => ({
				id: 'edit',
				label,
				icon: pencilIcon,
				onClick: (item) => void updateParams({ entity: item.id }),
			}),

			/** Raw entity view, only included in debug mode. */
			inspect: (): RowAction<T> | false =>
				isDebug && {
					id: 'inspect',
					label: __('View more', 'kudos-donations'),
					icon: pencilIcon,
					onClick: (item) => void updateParams({ entity: item.id }),
				},

			viewTransactions: (field: TransactionLink): RowAction<T> => ({
				id: 'view-transactions',
				label: __('View donations', 'kudos-donations'),
				icon: paymentIcon,
				onClick: (item) =>
					void setParams({
						page: 'kudos-transactions',
						where: { [field]: String(item.id) },
					}),
			}),

			remove: (label: string, confirmMessage: string): RowAction<T> => ({
				id: 'delete',
				label,
				icon: trashIcon,
				onClick: (item) =>
					confirmDelete(confirmMessage, () => handleDelete(item.id)),
			}),
		}),
		[handleDelete, isDebug, setParams, updateParams]
	);
};
