/* eslint-disable camelcase */
import {
	createContext,
	useCallback,
	useContext,
	useEffect,
	useMemo,
	useState,
} from '@wordpress/element';
import type { ReactNode, SyntheticEvent } from 'react';

import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { Icon } from '@wordpress/components';
import type { BaseEntity } from '../../types/entity';
import { useAdminQueryParams } from '../hooks';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

interface EntitiesContextValue<T extends BaseEntity = BaseEntity> {
	entities: T[];
	hasResolved: boolean;
	totalPages: number;
	totalItems: number;
	handleNew: (args?: Partial<T> | SyntheticEvent) => Promise<any>;
	handleUpdate: (data: Partial<T>) => Promise<any>;
	handleDelete: (entityId: number) => void;
	handleDuplicate: (entity: T) => void;
	fetchEntities: () => void;
	singularName: string;
	pluralName: string;
	entityType: string;
}

export interface EntityRestResponse<T extends BaseEntity> {
	items: T[];
	total: number;
	total_pages: number;
	per_page: number;
	paged: number;
}

const EntitiesContext = createContext<EntitiesContextValue<any> | null>(null);

interface EntitiesProviderProps {
	children: ReactNode;
	entityType: string;
	singularName: string;
	pluralName: string;
}

type EntityState<T> = {
	entities: T[];
	hasResolved: boolean;
	totalItems: number;
	totalPages: number;
};

export const EntitiesProvider = <T extends BaseEntity>({
	entityType,
	singularName,
	pluralName,
	children,
}: EntitiesProviderProps) => {
	const { params } = useAdminQueryParams();
	const { paged, order, orderby, where } = params;
	const { createSuccessNotice, createErrorNotice } =
		useDispatch(noticesStore);
	const [state, setState] = useState<EntityState<T>>({
		entities: [],
		hasResolved: false,
		totalItems: 0,
		totalPages: 1,
	});

	const fetchEntities = useCallback(async () => {
		try {
			setState((prev) => ({
				...prev,
				hasResolved: false,
			}));
			const args = { paged, orderby, order, where };
			apiFetch({
				path: addQueryArgs(`/kudos/v1/${entityType}`, args),
			}).then((response: EntityRestResponse<T>) => {
				setState({
					entities: response.items,
					hasResolved: true,
					totalItems: response.total,
					totalPages: response.total_pages,
				});
			});
		} catch (error) {
			setState((prev) => ({
				...prev,
				hasResolved: true,
			}));
		}
	}, [entityType, order, orderby, paged, where]);

	useEffect(() => {
		void fetchEntities();
	}, [fetchEntities]);

	const handleSave = useCallback(
		async (args = {}): Promise<T | null> => {
			try {
				const response = await apiFetch<T>({
					path: `/kudos/v1/${entityType}`,
					method: 'POST',
					data: args,
				});

				await fetchEntities();

				return response;
			} catch (error: any) {
				void createErrorNotice(
					sprintf(
						/* translators: %1$s is the entity type and %2$s is the error message. */
						__('Error creating %1$s: %2$s', 'kudos-donations'),
						singularName,
						error.message
					)
				);
				return null;
			}
		},
		[createErrorNotice, fetchEntities, entityType, singularName]
	);

	const handleUpdate = useCallback(
		async (data: Partial<T>) => {
			if (!data.id) {
				void createErrorNotice(
					__('Cannot update without an ID.', 'kudos-donations')
				);
				return null;
			}

			try {
				const { id, ...rest } = data;
				const response = await apiFetch<T>({
					path: `/kudos/v1/${entityType}/${id}`,
					method: 'PATCH',
					data: rest,
				});

				await fetchEntities();

				void createSuccessNotice(
					sprintf(
						/* translators: %s is the entity type singular name. */
						__('%s updated', 'kudos-donations'),
						singularName
					),
					{
						type: 'snackbar',
						icon: <Icon icon="saved" />,
					}
				);

				return response;
			} catch (error: any) {
				void createErrorNotice(
					sprintf(
						/* translators: %1$s is the entity type and %2$s is the error message. */
						__('Error updating %1$s: %2$s', 'kudos-donations'),
						singularName,
						error.message
					)
				);
				return null;
			}
		},
		[
			createErrorNotice,
			createSuccessNotice,
			entityType,
			fetchEntities,
			singularName,
		]
	);

	// Handles creating a entity.
	const handleNew = useCallback(
		async (args?: Partial<T>): Promise<any> => {
			// Set default arguments.
			const {
				title = sprintf(
					/* translators: %s is the entity type name. */
					__('New %s', 'kudos-donations'),
					singularName
				),
				...rest
			} = (args as Partial<T>) || {};

			const response = await handleSave({
				title,
				...rest,
			});

			if (response) {
				void createSuccessNotice(
					sprintf(
						/* translators: %s is the entity type name. */
						__('%s created', 'kudos-donations'),
						singularName
					),
					{
						type: 'snackbar',
						icon: <Icon icon="plus" />,
					}
				);
			}
			return response;
		},
		[createSuccessNotice, handleSave, singularName]
	);

	const handleDelete = useCallback(
		async (id: number): Promise<void> => {
			try {
				await apiFetch({
					path: `/kudos/v1/${entityType}/${id}`,
					method: 'DELETE',
				});
				await fetchEntities();
				await createSuccessNotice(
					sprintf(
						/* translators: %s is the entity type name. */
						__('%s deleted', 'kudos-donations'),
						singularName
					),
					{
						type: 'snackbar',
						icon: <Icon icon="trash" />,
					}
				);
			} catch (error: any) {
				await createErrorNotice(
					sprintf(
						/* translators: %1$s is the entity type and %2$s is the error message. */
						__('Error deleting %1$s: %2$s', 'kudos-donations'),
						singularName,
						error?.message ?? 'Unknown error'
					),
					{ type: 'snackbar' }
				);
			}
		},
		[
			entityType,
			fetchEntities,
			createSuccessNotice,
			singularName,
			createErrorNotice,
		]
	);

	// Prepares data for duplicating current entity.
	const handleDuplicate = useCallback(
		(entity: T) => {
			const { id, wp_post_id, ...rest } = entity;

			const data = {
				...rest,
				title: entity.title,
				created_at: new Date().toISOString(),
			} as Partial<T>;
			return handleSave(data);
		},
		[handleSave]
	);

	const data: EntitiesContextValue<T> = useMemo(
		() => ({
			entities: state.entities,
			hasResolved: state.hasResolved,
			totalItems: state.totalItems,
			totalPages: state.totalPages,
			handleNew,
			handleDuplicate,
			handleDelete,
			handleUpdate,
			singularName,
			pluralName,
			entityType,
			fetchEntities,
		}),
		[
			state.entities,
			state.hasResolved,
			state.totalItems,
			state.totalPages,
			handleDelete,
			handleDuplicate,
			handleNew,
			handleUpdate,
			singularName,
			pluralName,
			entityType,
			fetchEntities,
		]
	);

	return (
		<>
			<EntitiesContext.Provider value={data}>
				{children}
			</EntitiesContext.Provider>
		</>
	);
};

export const useEntitiesContext = <
	T extends BaseEntity,
>(): EntitiesContextValue<T> => {
	const context = useContext(EntitiesContext);
	if (!context) {
		throw new Error(
			'useEntitiesContext must be used within a EntitiesProvider'
		);
	}
	return context;
};
