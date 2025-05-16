import { Panel, PanelBody } from '@wordpress/components';
import React from 'react';

interface SectionPanelProps {
	title: string;
	children: React.ReactNode;
}

export const SectionPanel = ({
	title,
	children,
}: SectionPanelProps): React.ReactNode => (
	<Panel header={title}>
		<PanelBody>{children}</PanelBody>
	</Panel>
);
