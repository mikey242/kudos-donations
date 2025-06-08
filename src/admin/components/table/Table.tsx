import React from 'react';
import { Button, Flex, Spinner } from '@wordpress/components';
import type { IconType } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import type { Post } from '../../../types/posts';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { TableControls } from './TableControls';
import { useAdminQueryParams } from '../../hooks';
import { usePostsContext } from '../../contexts';
import { Filter } from './Filters';

export interface HeaderItem<T extends Post = Post> {
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
	align?: 'left' | 'right' | 'center' | 'justify' | 'start' | 'end';
	width?: string | number;
}

interface TableProps<T extends Post = Post> {
	headerItems: HeaderItem<T>[];
	posts?: T[];
	isLoading?: boolean;
	hasLoadedOnce?: boolean;
	totalPages?: number;
	totalItems?: number;
	filters?: Filter[];
}

export const Table = <T extends Post>({
	headerItems,
	posts,
	totalPages,
	totalItems,
	filters,
}: TableProps<T>): React.ReactNode => {
	const [cachedPosts, setCachedPosts] = useState<Post[]>(posts);
	const [hasLoadedOnce, setHasLoadedOnce] = useState<boolean>(false);
	const [isLoading, setIsLoading] = useState<boolean>(true);
	const { params, setParams } = useAdminQueryParams();
	const { pluralName, hasResolved } = usePostsContext();
	const { order, orderby } = params;

	useEffect(() => {
		setIsLoading(!hasResolved);
		if (hasResolved) {
			setCachedPosts(posts ?? []);
			setHasLoadedOnce(true);
		}
	}, [posts, hasResolved]);

	const getSortIcon = (value: string): IconType => {
		if (orderby !== value) {
			return 'sort';
		}
		return order === 'asc' ? 'arrow-up' : 'arrow-down';
	};

	const sort = useCallback(
		(newOrderBy: string) => {
			const prevOrderby = orderby;
			const prevOrder = order;
			const isSameColumn = prevOrderby === newOrderBy;

			const nextOrder =
				isSameColumn && prevOrder === 'asc' ? 'desc' : 'asc';

			void setParams({
				order: nextOrder,
				orderby: newOrderBy,
			});
		},
		[order, orderby, setParams]
	);

	return (
		<>
			<TableControls
				filters={filters}
				totalPages={totalPages}
				totalItems={totalItems}
			/>
			<table
				className="widefat striped rounded"
				style={{
					tableLayout:
						cachedPosts?.length === 0 || !cachedPosts
							? 'auto'
							: 'fixed',
				}}
			>
				<thead>
					<tr>
						{headerItems?.map((item) => (
							<th
								key={item.key}
								scope="col"
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
					{!cachedPosts ? (
						<TableMessage>
							<Spinner style={{ margin: 0, padding: '1em' }} />
						</TableMessage>
					) : cachedPosts?.length === 0 && hasLoadedOnce ? (
						<TableMessage>
							<p>
								{sprintf(
									// translators: %s is the plural post type name (e.g. Transactions)
									__('No %s', 'kudos-donations'),
									pluralName
								)}
							</p>
						</TableMessage>
					) : (
						cachedPosts?.map((post) => (
							<TableRow
								key={post.slug}
								post={post}
								columns={headerItems}
							/>
						))
					)}
				</tbody>
			</table>
			<TableControls totalPages={totalPages} totalItems={totalItems} />
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
						style={{
							width: column.width ?? 'auto',
							textAlign: column.align ?? 'left',
						}}
						key={column.key + post.id}
					>
						{column.valueCallback && column.valueCallback(post)}
					</td>
				);
			})}
		</tr>
	);
};
