import React from 'react';
import { Button, Flex, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useAdminContext } from './contexts';
import type { Post } from '../../types/wp';

interface HeaderItem<T extends Post = Post> {
	title: string | React.ReactNode;
	key: string;
	orderby?: string;
	valueCallback?: (post: T) => React.ReactNode;
}

interface TableProps<T extends Post = Post> {
	headerItems: HeaderItem<T>[];
	posts?: T[];
	isLoading?: boolean;
	hasLoadedOnce?: boolean;
}

export const Table = <T extends Post>({
	headerItems,
	posts,
	isLoading,
	hasLoadedOnce = false,
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
							<p>{__('No campaigns', 'kudos-donations')}</p>
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
		<tr>
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
