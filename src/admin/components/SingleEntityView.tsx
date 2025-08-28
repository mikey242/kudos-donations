import React from 'react';
import { useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { useAdminContext, useEntitiesContext } from '../contexts';
import { useAdminQueryParams } from '../hooks';
import type { BaseEntity } from '../../types/entity';
import {
	Flex,
	FlexItem,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalVStack as VStack,
	Button,
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

const renderField = (
	key: string,
	value: unknown,
	depth: number = 0
): React.ReactNode => {
	if (value === null || value === undefined) {
		return (
			<Row
				key={key}
				label={key}
				value={<em>{__('None', 'kudos-donations')}</em>}
			/>
		);
	}

	if (Array.isArray(value)) {
		return (
			<Panel
				key={key}
				initialOpen={depth < 1}
				header={`${key} (${__('Array', 'kudos-donations')})`}
			>
				{value.map((item, index) =>
					renderField(`${key} ${index + 1}`, item, depth + 1)
				)}
			</Panel>
		);
	}

	if (typeof value === 'object') {
		return (
			<Panel key={key} header={key} initialOpen={false}>
				{Object.entries(value)
					.sort(([a], [b]) => a.localeCompare(b))
					.map(([subKey, subValue]) =>
						renderField(subKey, subValue, depth + 1)
					)}
			</Panel>
		);
	}

	return <Row key={key} label={key} value={String(value)} />;
};

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
					// translators: %s is the entity singular name (e.g Transaction
					__('%s details', 'kudos-donations'),
					singularName
				)}
			>
				{Object.entries(entity)
					.sort(([aKey, aVal], [bKey, bVal]) => {
						const getPriority = (val: unknown) => {
							if (val === null || val === undefined) {
								return 0;
							}
							if (Array.isArray(val)) {
								return 1;
							}
							if (typeof val === 'object') {
								return 2;
							}
							return 0;
						};

						const priorityA = getPriority(aVal);
						const priorityB = getPriority(bVal);

						if (priorityA !== priorityB) {
							return priorityA - priorityB;
						}

						// Fallback to key sort
						return aKey.localeCompare(bKey);
					})
					.map(([key, value]) => renderField(key, value))}
			</Panel>
		</VStack>
	);
};

export default SingleEntityView;
