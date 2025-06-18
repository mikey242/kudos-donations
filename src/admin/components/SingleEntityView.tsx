import React from 'react';
import { useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { useAdminContext, useEntitiesContext } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import type { BaseEntity } from '../../types/entity';
import {
	Button,
	Flex,
	FlexItem,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { Panel } from './Panel';
const NavigationButtons = ({ onBack }): React.ReactNode => (
	<>
		<Button
			variant="secondary"
			icon="arrow-left"
			onClick={onBack}
			type="button"
		>
			{__('Back', 'kudos-donations')}
		</Button>
	</>
);

const Row = ({
	label,
	value,
}: {
	label: string;
	value: React.ReactNode;
}): React.ReactNode => (
	<Flex justify="space-between" align="center">
		<FlexItem
			style={{
				width: '160px',
				fontWeight: 600,
				flexShrink: 0,
			}}
		>
			{label}
		</FlexItem>
		<FlexItem
			style={{
				flexGrow: 1,
				overflowWrap: 'break-word',
				wordBreak: 'break-word',
				maxWidth: '100%',
			}}
		>
			{value}
		</FlexItem>
	</Flex>
);

interface PostEditProps {
	entity: BaseEntity;
}

const SingleEntityView = ({ entity }: PostEditProps): React.ReactNode => {
	const { setHeaderContent } = useAdminContext();
	const { updateParams } = useAdminQueryParams();
	const { singularName } = useEntitiesContext();

	useEffect(() => {
		setHeaderContent(
			<NavigationButtons
				onBack={() => {
					void updateParams({ entity: null, tab: null });
				}}
			/>
		);
		return () => {
			setHeaderContent(null);
		};
	}, [updateParams, setHeaderContent]);

	if (!entity) {
		return null;
	}

	return (
		<VStack spacing={4}>
			<Panel
				header={sprintf(
					// translators: %s is the entity type singular name (e.g. Transaction)
					__('%s details', 'kudos-donations'),
					singularName
				)}
			>
				{Object.entries(entity)
					.sort(([a], [b]) => a.localeCompare(b))
					.map(([key, value]) => (
						<Row
							key={key}
							label={key.replace(/_/g, ' ')}
							value={String(value)}
						/>
					))}
			</Panel>
		</VStack>
	);
};

export default SingleEntityView;
