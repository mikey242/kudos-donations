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
	CardFooter,
} from '@wordpress/components';
import React from 'react';
import { useState } from '@wordpress/element';

interface PanelProps {
	header: string;
	children: React.ReactNode;
	footer?: React.ReactNode;
	initialOpen?: boolean;
}

export const Panel = ({
	header,
	children,
	footer = null,
	initialOpen = true,
}: PanelProps) => {
	const [open, setOpen] = useState(initialOpen);
	return (
		<Card>
			<CardHeader
				style={{ cursor: 'pointer' }}
				onClick={() => setOpen(!open)}
			>
				<Heading size={16} level={3}>
					{header}
				</Heading>
			</CardHeader>
			{open && (
				<CardBody>
					<VStack spacing={5}>{children}</VStack>
				</CardBody>
			)}
			{footer && <CardFooter>{footer}</CardFooter>}
		</Card>
	);
};

export const PanelRow = ({ children }: { children: React.ReactNode }) => (
	<Flex gap={5} align="flex-start" justify="flex-start">
		{React.Children.map(children, (child, index) => (
			<FlexBlock key={index}>{child}</FlexBlock>
		))}
	</Flex>
);
