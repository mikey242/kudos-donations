import { Panel } from '../Panel';
import { __ } from '@wordpress/i18n';
import { TextAreaControl } from '../controls';
import React from 'react';

export const CustomCSSTab = () => {
	return (
		<Panel title={__('Custom CSS', 'kudos-donations')}>
			<TextAreaControl
				help="Enter your custom css here. This will only apply to the current campaign."
				label={__('Custom CSS', 'kudos-donations')}
				hideLabel={true}
				name="meta.custom_styles"
			/>
		</Panel>
	);
};
