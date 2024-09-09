import React from 'react';
import { Button, Flex, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useCampaignsContext } from '../contexts/CampaignsContext';

export const Table = ({ headerItems }) => {
	const { posts, sort, sortQuery, hasResolved } = useCampaignsContext();

	return (
		<>
			<table className="widefat fixed striped">
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
											sortQuery.orderby === item.orderby
												? sortQuery.order === 'asc'
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
					{!hasResolved ? (
						<tr>
							<td colSpan={headerItems.length}>
								<Flex justify="center">
									<Spinner />
								</Flex>
							</td>
						</tr>
					) : (
						<>
							{!posts?.length && (
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
							)}
							{posts?.map((post) => (
								<TableRow
									key={post.id}
									post={post}
									headerItems={headerItems}
								/>
							))}
						</>
					)}
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
