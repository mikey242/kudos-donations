import { __ } from '@wordpress/i18n';
import type { AdminTab } from '../../AdminTabPanel';
import { TextAreaControl } from '../../../controls';
import { useEffect, useRef } from '@wordpress/element';
import { useFormContext, useWatch } from 'react-hook-form';
import { Panel } from '../../../components';
import type {
	CodeEditorInstance,
	CodeEditorSettings,
} from '../../../../types/window-kudos';

const CustomCSSPanel = () => {
	const { setValue } = useFormContext();
	const customStyles = useWatch({ name: 'custom_styles' });
	const editorRef = useRef<HTMLTextAreaElement | null>(null);
	const codeEditorRef = useRef<CodeEditorInstance | null>(null);
	const editorId: string = 'css-editor';

	useEffect(() => {
		if (editorRef.current) {
			const editor = window?.wp.codeEditor?.initialize(
				editorId,
				window?.kudos?.codeEditor as CodeEditorSettings
			);
			codeEditorRef.current = editor ?? null;
			editor?.codemirror.on('change', (codemirror, change) => {
				// Ignore changes pushed in from the form (e.g. on reset).
				if (change.origin === 'setValue') {
					return;
				}
				setValue('custom_styles', codemirror.getValue(), {
					shouldValidate: true,
					shouldDirty: true,
				});
			});
		}
	}, [setValue]);

	// Keep the editor in sync when the form value changes externally (discard, save).
	useEffect(() => {
		const codemirror = codeEditorRef.current?.codemirror;
		const value = customStyles ?? '';
		if (codemirror && codemirror.getValue() !== value) {
			codemirror.setValue(value);
		}
	}, [customStyles]);

	return (
		<Panel header={__('Custom CSS', 'kudos-donations')}>
			<TextAreaControl
				ref={editorRef}
				id={editorId}
				help={__(
					'Enter your custom css here. This will only apply to the current campaign.',
					'kudos-donations'
				)}
				label={__('Custom CSS', 'kudos-donations')}
				hideLabelFromVision={true}
				name="custom_styles"
			/>
		</Panel>
	);
};

export const CustomCSSTab: AdminTab = {
	name: 'custom-css',
	title: __('Custom CSS', 'kudos-donations'),
	panels: [{ name: 'custom-css', content: <CustomCSSPanel /> }],
};
