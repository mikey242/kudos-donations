import type { BaseEntity } from '../../types/entity';
import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { Panel } from './Panel';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { useEntitiesContext } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import {
	Button,
	Fill,
	TextControl,
	SelectControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';
import { SLOT_HEADER_ACTIONS_EXTRA } from '../slot-names';

interface PostEditProps<T extends BaseEntity = BaseEntity> {
	data: T;
	fields: Field[];
}

interface FieldElement {
	value: string | number;
	label: string;
}

interface Field {
	elements?: FieldElement[];
	id: string;
	label: string;
	type: 'text' | 'integer' | 'datetime';
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
}: PostEditProps<T>): React.ReactNode => {
	const [formData, setFormData] = useState<T | null>(data ?? null);
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

	const handleFormChange = (fieldData: Record<string, unknown>) => {
		const merged = { ...formData, ...fieldData };
		setFormData(merged);
	};

	if (!formData) {
		return null;
	}

	const renderField = (field: Field) => {
		const value = formData[field.id as keyof T];

		if (field.elements && field.elements.length > 0) {
			return (
				<SelectControl
					key={field.id}
					label={field.label}
					value={String(value ?? '')}
					options={[
						{
							value: '',
							label: __('Select…', 'kudos-donations'),
						},
						...field.elements.map((el) => ({
							value: String(el.value),
							label: el.label,
						})),
					]}
					onChange={(newValue: string) =>
						handleFormChange({ [field.id]: newValue })
					}
					__next40pxDefaultSize
				/>
			);
		}

		switch (field.type) {
			case 'integer':
				return (
					<NumberControl
						key={field.id}
						label={field.label}
						value={String(value) ?? ''}
						onChange={(newValue) =>
							handleFormChange({
								[field.id]: newValue ? Number(newValue) : null,
							})
						}
						__next40pxDefaultSize
					/>
				);
			case 'datetime':
			case 'text':
			default:
				return (
					<TextControl
						key={field.id}
						label={field.label}
						value={String(value ?? '')}
						onChange={(newValue) =>
							handleFormChange({ [field.id]: newValue })
						}
						__next40pxDefaultSize
					/>
				);
		}
	};

	return (
		<>
			<Fill name={SLOT_HEADER_ACTIONS_EXTRA}>
				<NavigationButtons onBack={onBack} onSave={onSave} />
			</Fill>
			<Panel
				header={sprintf(
					// translators: %s is the entity singular name (e.g Transaction
					__('%s details', 'kudos-donations'),
					formData?.title
				)}
			>
				{fields.map(renderField)}
			</Panel>
		</>
	);
};
