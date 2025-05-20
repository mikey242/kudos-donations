import React from 'react';
import { Button, Flex, Spinner } from '@wordpress/components';
import type { IconType } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useAdminContext } from '../contexts';
import type { Post } from '../../../types/posts';
import { Pagination } from './Pagination';
import { useCallback } from '@wordpress/element';

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
	width?: string | number;
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
	const isFirstLoad = isLoading && !hasLoadedOnce;
	const getSortIcon = (orderby: string): IconType => {
		if (searchParams.get('orderby') !== orderby) {
			return 'sort';
		}
		return searchParams.get('order') === 'asc' ? 'arrow-up' : 'arrow-down';
	};

	const sort = useCallback(
		(orderby: string) => {
			const prevOrderby = searchParams.get('orderby');
			const prevOrder = searchParams.get('order');
			const isSameColumn = prevOrderby === orderby;

			const nextOrder =
				isSameColumn && prevOrder === 'asc' ? 'desc' : 'asc';
			const updates = [
				{ name: 'orderby', value: orderby },
				{ name: 'order', value: nextOrder },
			];

			const isMetaSort = orderby.startsWith('meta_value');
			const column = headerItems.find((item) => item.orderby === orderby);
			const metaKey = column?.key ?? '';

			if (isMetaSort && metaKey) {
				updates.push({ name: 'meta_key', value: metaKey });

				// Only apply meta_type when sorting numerically
				updates.push({
					name: 'meta_type',
					value: orderby === 'meta_value_num' ? 'NUMERIC' : '',
				});
			} else {
				// Clear meta sort params if not sorting on meta
				updates.push({ name: 'meta_key', value: '' });
				updates.push({ name: 'meta_type', value: '' });
			}

			updateParams(updates);
		},
		[searchParams, headerItems, updateParams]
	);

	return (
		<>
			<table
				className="widefat striped rounded"
				style={{
					tableLayout: isFirstLoad ? 'unset' : 'fixed',
				}}
			>
				<thead>
					<tr>
						{headerItems.map((item) => (
							<th
								key={item.key}
								style={
									item.width
										? { width: item.width }
										: undefined
								}
							>
								{item.orderby ? (
									<Button
										onClick={() => {
											sort(item.orderby);
										}}
										icon={getSortIcon(item.orderby)}
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
				<tbody className={isLoading ? 'is-loading' : 'is-loaded'}>
					{/* eslint-disable-next-line no-nested-ternary */}
					{isFirstLoad ? (
						<TableMessage>
							<Spinner style={{ margin: 0, padding: '1em' }} />
						</TableMessage>
					) : posts.length === 0 && hasLoadedOnce ? (
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
			{hasLoadedOnce && (
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
						className="table-cell"
						style={{ width: column.width ?? 'auto' }}
						key={column.key + post.id}
					>
						{column.valueCallback && column.valueCallback(post)}
					</td>
				);
			})}
		</tr>
	);
};
