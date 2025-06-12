import {
	Card,
	CardBody,
	CardHeader,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalHeading as Heading,
	Flex,
	FlexBlock,
} from '@wordpress/components';
import React from 'react';

interface PanelProps {
	header: string;
	children: React.ReactNode;
}

export const Panel = ({ header, children }: PanelProps) => (
	<Card>
		<CardHeader>
			<Heading size={16} level={3}>
				{header}
			</Heading>
		</CardHeader>
		<CardBody>
			<VStack spacing={5}>{children}</VStack>
		</CardBody>
	</Card>
);

export const PanelRow = ({ children }: { children: React.ReactNode }) => (
	<Flex gap={5} align="flex-start" justify="flex-start">
		{React.Children.map(children, (child, index) => (
			<FlexBlock key={index}>{child}</FlexBlock>
		))}
	</Flex>
);
