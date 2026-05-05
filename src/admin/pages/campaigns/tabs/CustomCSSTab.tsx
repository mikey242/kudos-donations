import { __ } from '@wordpress/i18n';
import { TextAreaControl } from '../../../controls';
import { useEffect, useRef } from '@wordpress/element';
import type { ReactNode } from 'react';
import { useFormContext } from 'react-hook-form';
import { Panel } from '../../../components';
import { PanelList } from '../../AdminTabPanel';

const CustomCSSPanel = () => {
	const { setValue } = useFormContext();
	const editorRef = useRef<HTMLTextAreaElement | null>(null);
	const editorId: string = 'css-editor';

	useEffect(() => {
		if (editorRef.current) {
			const editor = window?.wp.codeEditor?.initialize(
				editorId,
				window?.kudos.codeEditor
			);
			editor?.codemirror.on('change', () => {
				const value = editor.codemirror.getValue();
				setValue('custom_styles', value, { shouldValidate: true });
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
				name="custom_styles"
			/>
		</Panel>
	);
};

export const CustomCSSTab = (): ReactNode => (
	<PanelList
		namespace="kudosCampaignPanels"
		tabName="custom-css"
		defaultPanels={[{ name: 'custom-css', content: <CustomCSSPanel /> }]}
	/>
);
