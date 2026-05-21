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
import { useLayoutEffect, useRef, useState } from '@wordpress/element';
import React from 'react';
import { useAdminQueryParams } from '../hooks';

const PanelFooter = ({ children }: { children: React.ReactNode }) => (
	<>{children}</>
);
PanelFooter.displayName = 'Panel.Footer';

export interface PanelProps {
	name?: string;
	header: string;
	headerExtra?: React.ReactNode;
	children: React.ReactNode;
	initialOpen?: boolean;
	spacing?: number;
	disabled?: boolean;
	collapsable?: boolean;
}

export const Panel = ({
	name = null,
	header,
	headerExtra,
	children,
	initialOpen = true,
	spacing = 5,
	disabled = false,
	collapsable = true,
}: PanelProps) => {
	const {
		params: { panel },
	} = useAdminQueryParams();
	const isHighlighted = name && panel === name;
	const [open, setOpen] = useState(initialOpen || !!isHighlighted);
	const ref = useRef(null);
	const childArray = React.Children.toArray(children);
	const footer = childArray.find(
		(child) => React.isValidElement(child) && child.type === PanelFooter
	) as React.ReactElement | undefined;
	const bodyChildren = childArray.filter(
		(child) => !(React.isValidElement(child) && child.type === PanelFooter)
	);

	useLayoutEffect(() => {
		if (isHighlighted && ref.current) {
			ref.current.scrollIntoView({
				block: 'center',
				behavior: 'smooth',
			});
			ref.current.classList.add('selected');
			setTimeout(() => {
				ref.current.classList.remove('selected');
			}, 2000);
		}
	}, [isHighlighted]);

	return (
		<Card ref={ref} className={'kudos-admin-panel'}>
			<CardHeader
				style={{ cursor: collapsable ? 'pointer' : 'default' }}
				onClick={collapsable ? () => setOpen(!open) : null}
			>
				<Heading size={16} level={3}>
					{header}
				</Heading>
				{headerExtra && <>{headerExtra}</>}
			</CardHeader>
			<Disabled isDisabled={disabled}>
				{open && (
					<CardBody style={disabled ? { opacity: 0.5 } : undefined}>
						<VStack spacing={spacing}>{bodyChildren}</VStack>
					</CardBody>
				)}
				{footer && <CardFooter>{footer}</CardFooter>}
			</Disabled>
		</Card>
	);
};

Panel.Footer = PanelFooter;

export const PanelRow = ({ children }: { children: React.ReactNode }) => (
	<Flex gap={5} align="flex-start" justify="flex-start">
		{React.Children.map(children, (child, index) => (
			<FlexBlock key={index}>{child}</FlexBlock>
		))}
	</Flex>
);
