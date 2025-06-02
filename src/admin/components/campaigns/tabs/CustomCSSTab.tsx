import { __ } from '@wordpress/i18n';
import { TextAreaControl } from '../../controls';
import React from 'react';
import { useEffect, useRef } from '@wordpress/element';
import { useFormContext } from 'react-hook-form';
import { Panel } from '../../Panel';

export const CustomCSSTab = (): React.ReactNode => {
	const { setValue } = useFormContext(); // Get methods from React Hook Form
	const editorRef = useRef<HTMLTextAreaElement | null>(null); // Ref for the textarea
	const editorId: string = 'css-editor'; // Unique ID for the textarea

	useEffect(() => {
		if (editorRef.current) {
			// Initialize the CodeMirror editor
			const editor = window?.wp.codeEditor?.initialize(
				editorId,
				window?.kudos.codeEditor
			);

			// Update the form state whenever the CodeMirror content changes
			editor?.codemirror.on('change', () => {
				const value = editor.codemirror.getValue();
				setValue('meta.custom_styles', value, { shouldValidate: true });
			});
		}
	}, [setValue]);

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
				name="meta.custom_styles"
			/>
		</Panel>
	);
};
