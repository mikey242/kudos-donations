import React from 'react';
import { Button, Flex, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useCampaignsContext, useAdminContext } from './contexts';

export const Table = ({ headerItems }) => {
	const { posts, hasResolved } = useCampaignsContext();
	const { searchParams, updateParams } = useAdminContext();

	const sort = (orderby) => {
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
							<th key={item.title}>
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
					<>
						{!hasResolved && !posts?.length && (
							<TableMessage>
								<Spinner />
							</TableMessage>
						)}
						{!posts?.length && hasResolved ? (
							<TableMessage>
								<p>{__('No campaigns', 'kudos-donations')}</p>
							</TableMessage>
						) : (
							posts?.map((post) => {
								return (
									<TableRow
										key={post.slug}
										post={post}
										columns={headerItems}
									/>
								);
							})
						)}
					</>
				</tbody>
			</table>
		</>
	);
};

const TableMessage = ({ children, colspan = 100 }) => (
	<tr>
		<td colSpan={colspan}>
			<Flex justify="center">{children}</Flex>
		</td>
	</tr>
);

const TableRow = ({ post, columns }) => {
	return (
		<tr>
			{columns.map((column) => {
				return (
					<td
						style={{ verticalAlign: 'middle' }}
						key={column.title + post.id}
					>
						{column.valueCallback && column.valueCallback(post)}
					</td>
				);
			})}
		</tr>
	);
};
