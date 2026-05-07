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
	Disabled,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import React from 'react';

interface PanelProps {
	header: string;
	headerExtra?: React.ReactNode;
	children: React.ReactNode;
	footer?: React.ReactNode;
	initialOpen?: boolean;
	spacing?: number;
	disabled?: boolean;
}

export const Panel = ({
	header,
	headerExtra,
	children,
	footer = null,
	initialOpen = true,
	spacing = 5,
	disabled = false,
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
				{headerExtra && <>{headerExtra}</>}
			</CardHeader>
			<Disabled isDisabled={disabled}>
				{open && (
					<CardBody style={disabled && { opacity: '0.5' }}>
						<VStack spacing={spacing}>{children}</VStack>
					</CardBody>
				)}
				{footer && <CardFooter>{footer}</CardFooter>}
			</Disabled>
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
