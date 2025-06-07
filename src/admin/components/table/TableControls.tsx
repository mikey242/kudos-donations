import React from 'react';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	Button,
	Flex,
	FlexItem,
} from '@wordpress/components';
import { Pagination } from './Pagination';
import { useAdminQueryParams } from '../../hooks';
import { __ } from '@wordpress/i18n';

interface TableControlsProps {
	totalPages: number;
	totalItems: number;
}

export const TableControls = ({
	totalItems,
	totalPages,
}: TableControlsProps): React.ReactNode => {
	const { resetFilterParams, hasActiveFilters } = useAdminQueryParams();

	return (
		<>
			<Spacer marginTop={'3'} />
			<Flex justify="space-between">
				<FlexItem>
					{hasActiveFilters && (
						<Button
							size="compact"
							variant={'tertiary'}
							icon={'dismiss'}
							onClick={resetFilterParams}
						>
							{__('Reset view', 'kudos-donations')}
						</Button>
					)}
				</FlexItem>
				{totalPages > 1 && (
					<FlexItem>
						<Pagination
							totalPages={totalPages}
							totalItems={totalItems}
						/>
					</FlexItem>
				)}
			</Flex>
			<Spacer marginTop={'3'} />
		</>
	);
};
