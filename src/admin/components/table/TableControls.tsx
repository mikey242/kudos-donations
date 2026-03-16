import React from 'react';
import { Flex, FlexItem } from '@wordpress/components';
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
			<Flex wrap={true} justify="space-between">
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
		</>
	);
};
