import type { BaseEntity } from '../../types/entity';
import React from 'react';
// @ts-ignore
import { DataForm } from '@wordpress/dataviews/wp';
import { __, sprintf } from '@wordpress/i18n';
import { Panel } from './Panel';
import { useEffect } from '@wordpress/element';
import { useAdminContext, useEntitiesContext } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import { Button } from '@wordpress/components';

interface PostEditProps {
	data: BaseEntity;
	fields: Field[];
	form: {
		fields: string[];
		layout?: object;
	};
}

interface Field {
	elements?: object;
	id: string;
	label: string;
	type: string;
}

export const NavigationButtons = ({ onBack }): React.ReactNode => (
	<>
		<Button
			variant="secondary"
			icon="arrow-left"
			onClick={onBack}
			type="button"
		>
			{__('Back', 'kudos-donations')}
		</Button>
	</>
);

export const SingleEntityEdit = ({
	data,
	fields,
	form,
}: PostEditProps): React.ReactNode => {
	const { setHeaderContent } = useAdminContext();
	const { updateParams } = useAdminQueryParams();
	const { handleUpdate } = useEntitiesContext();

	useEffect(() => {
		setHeaderContent(
			<NavigationButtons
				onBack={() => {
					void updateParams({ entity: null, tab: null });
				}}
			/>
		);
		return () => {
			setHeaderContent(null);
		};
	}, [updateParams, setHeaderContent]);

	const handleFormChange = (formData: Object) => {
		const merged = { ...data, ...formData };
		void handleUpdate(merged);
	};

	if (!data) {
		return null;
	}

	return (
		<Panel
			header={sprintf(
				// translators: %s is the entity singular name (e.g Transaction
				__('%s details', 'kudos-donations'),
				data?.title
			)}
		>
			<DataForm
				data={data}
				fields={fields}
				form={{
					layout: {
						labelPosition: undefined,
						openAs: 'modal',
						type: 'panel',
					},
					...form,
				}}
				onChange={(e: Object) => {
					handleFormChange(e);
				}}
			/>
		</Panel>
	);
};
