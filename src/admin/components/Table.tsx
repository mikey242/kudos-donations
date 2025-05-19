import React from 'react';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	Button,
	Flex,
	Spinner,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { useAdminContext } from './contexts';
import type { Post } from '../../types/wp';
import {
	ChevronDoubleLeftIcon,
	ChevronDoubleRightIcon,
	ChevronLeftIcon,
	ChevronRightIcon,
} from '@heroicons/react/24/outline';
import { useEffect, useRef, useState } from '@wordpress/element';
import { Input } from '@headlessui/react';

interface HeaderItem<T extends Post = Post> {
	title: string | React.ReactNode;
	key: string;
	orderby?:
		| 'author'
		| 'date'
		| 'id'
		| 'include'
		| 'modified'
		| 'parent'
		| 'relevance'
		| 'slug'
		| 'include_slugs'
		| 'title'
		| string;
	valueCallback?: (post: T) => React.ReactNode;
}

interface TableProps<T extends Post = Post> {
	headerItems: HeaderItem<T>[];
	posts?: T[];
	isLoading?: boolean;
	hasLoadedOnce?: boolean;
	totalPages?: number;
	totalItems?: number;
}

export const Table = <T extends Post>({
	headerItems,
	posts,
	isLoading,
	hasLoadedOnce = false,
	totalPages,
	totalItems,
}: TableProps<T>): React.ReactNode => {
	const { searchParams, updateParams } = useAdminContext();

	const sort = (orderby: string) => {
		const prevOrderby = searchParams.get('orderby');
		const prevOrder = searchParams.get('order');

		updateParams([
			{ name: 'orderby', value: orderby },
			{
				name: 'order',
				value:
					prevOrderby !== orderby || prevOrder === 'desc'
						? 'asc'
						: 'desc',
			},
		]);
	};

	return (
		<>
			{!isLoading && totalPages > 1 && (
				<Pagination totalPages={totalPages} totalItems={totalItems} />
			)}
			<table className="widefat striped rounded">
				<thead>
					<tr>
						{headerItems.map((item) => (
							<th key={item.key}>
								{item.orderby ? (
									<Button
										onClick={() => {
											sort(item.orderby);
										}}
										icon={
											// eslint-disable-next-line no-nested-ternary
											searchParams.get('orderby') ===
											item.orderby
												? searchParams.get('order') ===
													'asc'
													? 'arrow-up'
													: 'arrow-down'
												: 'sort'
										}
									>
										{item.title}
									</Button>
								) : (
									<span>{item.title} </span>
								)}
							</th>
						))}
					</tr>
				</thead>
				<tbody>
					{/* eslint-disable-next-line no-nested-ternary */}
					{isLoading ? (
						<TableMessage>
							<Spinner style={{ margin: 0, padding: '1em' }} />
						</TableMessage>
					) : !posts.length && hasLoadedOnce ? (
						<TableMessage>
							<p>{__('No posts', 'kudos-donations')}</p>
						</TableMessage>
					) : (
						posts.map((post) => (
							<TableRow
								key={post.slug}
								post={post}
								columns={headerItems}
							/>
						))
					)}
				</tbody>
			</table>
			{!isLoading && totalPages > 1 && (
				<Pagination totalPages={totalPages} totalItems={totalItems} />
			)}
		</>
	);
};

interface TableMessageProps {
	children: React.ReactNode;
	colspan?: number;
}

const TableMessage = ({
	children,
	colspan = 100,
}: TableMessageProps): React.ReactNode => (
	<tr>
		<td colSpan={colspan}>
			<Flex justify="center">{children}</Flex>
		</td>
	</tr>
);

interface TableRowProps<T extends Post> {
	post: T;
	columns: HeaderItem<T>[];
}

const TableRow = <T extends Post>({ post, columns }: TableRowProps<T>) => {
	return (
		<tr role="row">
			{columns.map((column) => {
				return (
					<td
						style={{ verticalAlign: 'middle' }}
						key={column.key + post.id}
					>
						{column.valueCallback && column.valueCallback(post)}
					</td>
				);
			})}
		</tr>
	);
};

interface PaginationProps {
	totalPages: number;
	totalItems: number;
}

const Pagination = ({
	totalItems,
	totalPages,
}: PaginationProps): React.ReactNode => {
	const { searchParams, updateParam } = useAdminContext();
	const currentPage = parseInt(searchParams.get('paged') ?? '1', 10);
	const [isEditing, setIsEditing] = useState(false);
	const [inputValue, setInputValue] = useState(String(currentPage));
	const inputRef = useRef<HTMLInputElement>(null);

	useEffect(() => {
		if (isEditing && inputRef.current) {
			inputRef.current.focus();
		}
	}, [isEditing]);

	const goToPage = (page: number) => {
		const safePage = Math.max(1, Math.min(page, totalPages));
		updateParam('paged', String(safePage));
		setIsEditing(false);
	};
	const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
		if (e.key === 'Enter') {
			goToPage(Number(inputValue));
		} else if (e.key === 'Escape') {
			setInputValue(String(currentPage));
			setIsEditing(false);
		}
	};

	return (
		<>
			<Spacer marginTop={'3'} />
			<Flex justify="center">
				{/* First Page */}
				<Button
					variant="link"
					onClick={() => updateParam('paged', '1')}
					disabled={currentPage === 1}
					label={__('First page', 'kudos-donations')}
					showTooltip
				>
					<ChevronDoubleLeftIcon style={{ width: 20, height: 20 }} />
				</Button>

				{/* Previous Page */}
				<Button
					variant="link"
					onClick={() =>
						updateParam('paged', String(currentPage - 1))
					}
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
						padding: '0 1rem',
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
						<Input
							ref={inputRef}
							type="number"
							max={totalPages}
							min={1}
							value={inputValue}
							onChange={(e) => setInputValue(e.target.value)}
							onBlur={() => goToPage(Number(inputValue))}
							onKeyDown={handleKeyDown}
							style={{
								width: '4em',
								textAlign: 'center',
								fontSize: '1rem',
							}}
						/>
					) : (
						<>
							{sprintf(
								// translators: %1$d is the current page, %2$d is the total number of pages
								__('Page %1$d of %2$d', 'kudos-donations'),
								currentPage,
								totalPages
							)}{' '}
							(
							{sprintf(
								// translators: %1$d is the total number of items
								__('%1$d items', 'kudos-donations'),
								totalItems
							)}
							)
						</>
					)}
				</span>

				{/* Next Page */}
				<Button
					variant="link"
					onClick={() =>
						updateParam('paged', String(currentPage + 1))
					}
					disabled={currentPage >= totalPages}
					label={__('Next page', 'kudos-donations')}
					showTooltip
				>
					<ChevronRightIcon style={{ width: 20, height: 20 }} />
				</Button>

				{/* Last Page */}
				<Button
					variant="link"
					onClick={() => updateParam('paged', String(totalPages))}
					disabled={currentPage >= totalPages}
					label={__('Last page', 'kudos-donations')}
					showTooltip
				>
					<ChevronDoubleRightIcon style={{ width: 20, height: 20 }} />
				</Button>
			</Flex>
			<Spacer marginTop={'3'} />
		</>
	);
};
