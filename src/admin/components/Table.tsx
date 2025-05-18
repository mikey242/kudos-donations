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
			<Spacer marginTop={'5'} />
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
	return (
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
				onClick={() => updateParam('paged', String(currentPage - 1))}
				disabled={currentPage <= 1}
				label={__('Previous page', 'kudos-donations')}
				showTooltip
			>
				<ChevronLeftIcon style={{ width: 20, height: 20 }} />
			</Button>

			{/* Page Info */}
			<span style={{ padding: '0 1rem', lineHeight: '2rem' }}>
				{sprintf(
					// translators: %1$d is current page, %1$d is the total number of pages
					__('Page %1$d of %2$d', 'kudos-donations'),
					currentPage,
					totalPages
				)}{' '}
				({totalItems} {__('items', 'kudos-donations')})
			</span>

			{/* Next Page */}
			<Button
				variant="link"
				onClick={() => updateParam('paged', String(currentPage + 1))}
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
	);
};
