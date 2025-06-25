import React from 'react';
import { useEffect, useState } from '@wordpress/element';
import { useEntitiesContext } from '../contexts';
import type { BaseEntity } from '../../types/entity';
import { useAdminQueryParams } from '../hooks';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

interface EntityPageProps {
	renderTable: (
		editPost: (id: string | number) => void,
		newPost: (e: React.SyntheticEvent | Partial<BaseEntity>) => void
	) => React.ReactNode;
	renderEdit?: (post: BaseEntity) => React.ReactNode;
}

export const EntityPage = ({
	renderTable,
	renderEdit,
}: EntityPageProps): React.ReactNode => {
	const { params, updateParams } = useAdminQueryParams();
	const { entity: entityId } = params;
	const [currentEntity, setCurrentEntity] = useState<BaseEntity | null>(null);
	const { entities, handleNew, entityType, hasResolved } =
		useEntitiesContext<BaseEntity>();

	const newPost = async () => {
		await handleNew().then((response) => {
			if (response?.id) {
				updateParams({ entity: response.id });
			}
		});
	};

	const editPost = (id: number) => {
		void updateParams({ entity: id });
	};

	useEffect(() => {
		if (entityId && hasResolved) {
			const found = entities.find(
				(entity) => Number(entity.id) === Number(entityId)
			);
			if (found) {
				setCurrentEntity(found);
			} else {
				apiFetch({
					path: addQueryArgs(`/kudos/v1/${entityType}/${entityId}`),
				}).then((response: BaseEntity) => {
					setCurrentEntity(response);
				});
			}
		}
	}, [entityId, entities, entityType, hasResolved]);

	return (
		<>
			{entityId && renderEdit ? (
				<div className="admin-wrap"> {renderEdit(currentEntity)}</div>
			) : (
				<div className="admin-wrap-wide">
					{renderTable(editPost, newPost)}
				</div>
			)}
		</>
	);
};
