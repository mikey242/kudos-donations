import React from 'react';
import { useFormContext, useFormState } from 'react-hook-form';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

interface StickySaveBarProps {
	formId: string;
	isSaving?: boolean;
}

export const StickySaveBar = ({
	formId,
	isSaving,
}: StickySaveBarProps): React.ReactNode => {
	const { control, reset } = useFormContext();
	const { isDirty, isSubmitting } = useFormState({ control });
	const saving = isSaving ?? isSubmitting;

	if (!isDirty) {
		return null;
	}

	return (
		<div className="kudos-sticky-save-bar">
			<span className="kudos-sticky-save-bar__label">
				{__('Unsaved changes', 'kudos-donations')}
			</span>
			<Button
				variant="tertiary"
				onClick={() => reset()}
				disabled={saving}
			>
				{__('Discard', 'kudos-donations')}
			</Button>
			<Button
				variant="primary"
				type="submit"
				form={formId}
				isBusy={saving}
				disabled={saving}
			>
				{__('Save', 'kudos-donations')}
			</Button>
		</div>
	);
};
