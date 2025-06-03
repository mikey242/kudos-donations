import {
	Card,
	CardBody,
	CardHeader,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
} from '@wordpress/components';
import React from 'react';

interface PanelProps {
	header: string;
	children: React.ReactNode;
}

export const Panel = ({ header, children }: PanelProps) => (
	<Card>
		<CardHeader>{header}</CardHeader>
		<CardBody>
			<VStack spacing={5}>{children}</VStack>
		</CardBody>
	</Card>
);
