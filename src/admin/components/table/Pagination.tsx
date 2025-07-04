import React from 'react';
import { useEffect, useRef, useState } from '@wordpress/element';
import { Button, Flex, TextControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import {
	ChevronDoubleLeftIcon,
	ChevronDoubleRightIcon,
	ChevronLeftIcon,
	ChevronRightIcon,
} from '@heroicons/react/24/outline';
import { useAdminQueryParams } from '../../hooks';

interface PaginationProps {
	totalPages: number;
	totalItems: number;
}

export const Pagination = ({
	totalItems,
	totalPages,
}: PaginationProps): React.ReactNode => {
	const { params, updateParams } = useAdminQueryParams();
	const { paged: currentPage } = params;
	const [isEditing, setIsEditing] = useState(false);
	const [inputValue, setInputValue] = useState<number>(currentPage);
	const inputRef = useRef<HTMLInputElement>(null);

	useEffect(() => {
		if (isEditing && inputRef.current) {
			inputRef.current.focus();
		}
	}, [isEditing]);

	useEffect(() => {
		if (!isEditing) {
			setInputValue(currentPage);
		}
	}, [currentPage, isEditing]);

	const goToPage = async (page: number) => {
		const sanitizedPage = Math.min(Math.max(1, page), totalPages);
		await updateParams({ paged: sanitizedPage });
		setIsEditing(false);
	};
	const handleKeyDown = async (e: React.KeyboardEvent<HTMLInputElement>) => {
		if (e.key === 'Enter') {
			await goToPage(Number(inputValue));
		} else if (e.key === 'Escape') {
			setInputValue(currentPage);
			setIsEditing(false);
		}
	};

	return (
		<Flex justify="flex-end" align="center">
			{/* First Page */}
			<Button
				variant="link"
				onClick={() => goToPage(1)}
				disabled={currentPage === 1}
				label={__('First page', 'kudos-donations')}
				showTooltip
			>
				<ChevronDoubleLeftIcon style={{ width: 20, height: 20 }} />
			</Button>

			{/* Previous Page */}
			<Button
				variant="link"
				onClick={() => goToPage(currentPage - 1)}
				disabled={currentPage <= 1}
				label={__('Previous page', 'kudos-donations')}
				showTooltip
			>
				<ChevronLeftIcon style={{ width: 20, height: 20 }} />
			</Button>

			{/* Page Info */}
			<span
				role="button"
				tabIndex={0}
				style={{
					lineHeight: '2rem',
					cursor: 'pointer',
				}}
				onClick={() => setIsEditing(true)}
				onKeyDown={(e) => {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						setIsEditing(true);
					}
				}}
			>
				{isEditing ? (
					<TextControl
						ref={inputRef}
						type="number"
						max={totalPages}
						min={1}
						value={inputValue}
						onChange={(value) => setInputValue(Number(value))}
						onBlur={() => goToPage(Number(inputValue))}
						onKeyDown={handleKeyDown}
						style={{
							width: '4em',
							textAlign: 'center',
							fontSize: '1rem',
						}}
						__nextHasNoMarginBottom
					/>
				) : (
					<>
						{sprintf(
							// translators: %1$d is the current page, %2$d is the total number of pages
							__('Page %1$d of %2$d', 'kudos-donations'),
							currentPage ?? 0,
							totalPages ?? 0
						)}{' '}
						(
						{sprintf(
							// translators: %1$d is the total number of items
							__('%1$d items', 'kudos-donations'),
							totalItems ?? 0
						)}
						)
					</>
				)}
			</span>

			{/* Next Page */}
			<Button
				variant="link"
				onClick={() => goToPage(currentPage + 1)}
				disabled={currentPage >= totalPages}
				label={__('Next page', 'kudos-donations')}
				showTooltip
			>
				<ChevronRightIcon style={{ width: 20, height: 20 }} />
			</Button>

			{/* Last Page */}
			<Button
				variant="link"
				onClick={() => goToPage(totalPages)}
				disabled={currentPage >= totalPages}
				label={__('Last page', 'kudos-donations')}
				showTooltip
			>
				<ChevronDoubleRightIcon style={{ width: 20, height: 20 }} />
			</Button>
		</Flex>
	);
};
