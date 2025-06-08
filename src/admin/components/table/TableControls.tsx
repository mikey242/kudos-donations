import React from 'react';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	Flex,
	FlexItem,
} from '@wordpress/components';
import { Pagination } from './Pagination';
import { Filter, Filters } from './Filters';

interface TableControlsProps {
	totalPages: number;
	totalItems: number;
	filters?: Filter[];
}

export const TableControls = ({
	totalItems,
	totalPages,
	filters,
}: TableControlsProps): React.ReactNode => {
	return (
		<>
			<Spacer marginTop={'3'} />
			<Flex justify="space-between">
				<FlexItem>
					<Filters filters={filters} />
				</FlexItem>
				<FlexItem>
					<Pagination
						totalPages={totalPages}
						totalItems={totalItems}
					/>
				</FlexItem>
			</Flex>
			<Spacer marginTop={'3'} />
		</>
	);
};
