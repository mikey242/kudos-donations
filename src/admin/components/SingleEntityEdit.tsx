import type { BaseEntity } from '../../types/entity';
import React from 'react';
// @ts-ignore
import { DataForm } from '@wordpress/dataviews/wp';
import { __, sprintf } from '@wordpress/i18n';
import { Panel } from './Panel';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { useAdminContext, useEntitiesContext } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import { Button } from '@wordpress/components';

interface PostEditProps<T extends BaseEntity = BaseEntity> {
	data: T;
	fields: Field[];
	form: {
		fields: string[];
		layout?: {
			labelPosition?: string;
			type?: 'regular' | 'panel' | 'card';
		};
	};
}

interface Field {
	elements?: Record<string, unknown>;
	id: string;
	label: string;
	type: string;
}

interface NavigationButtonsProps {
	onBack: () => void;
	onSave: () => void;
}

const NavigationButtons = ({
	onBack,
	onSave,
}: NavigationButtonsProps): React.ReactNode => (
	<>
		<Button
			variant="secondary"
			icon="arrow-left"
			onClick={onBack}
			type="button"
		>
			{__('Back', 'kudos-donations')}
		</Button>
		<Button variant="primary" onClick={onSave} type="button">
			{__('Save', 'kudos-donations')}
		</Button>
	</>
);

export const SingleEntityEdit = <T extends BaseEntity>({
	data,
	fields,
	form,
}: PostEditProps<T>): React.ReactNode => {
	const [formData, setFormData] = useState<T | null>(data ?? null);
	const { setHeaderContent } = useAdminContext();
	const { updateParams } = useAdminQueryParams();
	const { handleUpdate } = useEntitiesContext();

	const onSave = useCallback(() => {
		void handleUpdate(formData);
	}, [formData, handleUpdate]);

	const onBack = useCallback(() => {
		void updateParams({ entity: null, tab: null });
	}, [updateParams]);

	useEffect(() => {
		setFormData(data);
	}, [data]);

	useEffect(() => {
		setHeaderContent(<NavigationButtons onBack={onBack} onSave={onSave} />);
		return () => {
			setHeaderContent(null);
		};
	}, [onBack, onSave, setHeaderContent]);

	const handleFormChange = (fieldData: Record<string, unknown>) => {
		const merged = { ...formData, ...fieldData };
		setFormData(merged);
	};

	if (!formData) {
		return null;
	}

	return (
		<Panel
			header={sprintf(
				// translators: %s is the entity singular name (e.g Transaction
				__('%s details', 'kudos-donations'),
				formData?.title
			)}
		>
			<DataForm
				data={formData}
				fields={fields}
				form={{
					layout: {
						labelPosition: undefined,
						type: 'regular',
					},
					...form,
				}}
				onChange={(e: Record<string, unknown>) => {
					handleFormChange(e);
				}}
			/>
		</Panel>
	);
};
