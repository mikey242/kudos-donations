import React from 'react';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	Flex,
} from '@wordpress/components';
import { Pagination } from './Pagination';

interface TableControlsProps {
	totalPages: number;
	totalItems: number;
}

export const TableControls = ({
	totalItems,
	totalPages,
}: TableControlsProps): React.ReactNode => {
	return (
		<>
			<Spacer marginTop={'3'} />
			<Flex justify="space-between">
				<Pagination totalPages={totalPages} totalItems={totalItems} />
			</Flex>
			<Spacer marginTop={'3'} />
		</>
	);
};
