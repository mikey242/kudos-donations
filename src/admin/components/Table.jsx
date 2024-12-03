import React from 'react';
import { Button, Flex } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useCampaignsContext, useAdminContext } from './contexts';

export const Table = ({ headerItems }) => {
	const { posts, sort, hasResolved } = useCampaignsContext();
	const { searchParams } = useAdminContext();

	return (
		<>
			<table className="widefat striped">
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
						{!posts?.length && hasResolved ? (
							<tr>
								<td colSpan={headerItems.length}>
									<Flex justify="center">
										<p>
											{__(
												'No campaigns',
												'kudos-donations'
											)}
										</p>
									</Flex>
								</td>
							</tr>
						) : (
							posts?.map((post) => {
								return (
									<TableRow
										key={post.slug}
										post={post}
										headerItems={headerItems}
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

const TableRow = ({ post, headerItems }) => {
	return (
		<tr>
			{headerItems.map((column) => {
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
