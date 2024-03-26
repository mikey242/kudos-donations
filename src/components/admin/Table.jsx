import React from 'react';
import { Fragment } from '@wordpress/element';
import Panel from '../Panel';
import { __ } from '@wordpress/i18n';
import { ChevronUpDownIcon, ChevronUpIcon } from '@heroicons/react/20/solid';

import { StringParam, useQueryParams, withDefault } from 'use-query-params';
import TableRow from './TableRow';
import { clsx } from 'clsx';

const Table = ({ headerItems, tableData, updatePost }) => {
	const [sortQuery, setSortQuery] = useQueryParams({
		order: withDefault(StringParam, 'desc'),
		orderby: withDefault(StringParam, 'date'),
	});
	const sort = (orderby) => {
		setSortQuery((prev) => {
			return {
				orderby,
				order:
					prev.orderby !== orderby || prev.order === 'desc'
						? 'asc'
						: 'desc',
			};
		});
	};

	const renderHeaderItem = (item) => {
		return (
			<>
				<div
					className={clsx(item.headerClass, 'table-cell px-3 py-3.5')}
				>
					{item.orderby ? (
						<button
							className="inline-flex"
							onClick={() => {
								sort(item.orderby);
							}}
						>
							{item.title}
							{sortQuery.orderby === item.orderby ? (
								<ChevronUpIcon
									className={`${
										sortQuery.order === 'asc' &&
										'rotate-180'
									} ml-2 w-4`}
								/>
							) : (
								<ChevronUpDownIcon className="ml-2 w-4" />
							)}
						</button>
					) : (
						<span>{item.title} </span>
					)}
				</div>
			</>
		);
	};

	return (
		<>
			{headerItems && tableData && (
				<Panel
					className="overflow-x-auto"
					title={__('Your campaigns', 'kudos-donations')}
				>
					<div className="table border-collapse min-w-full text-left sm:rounded-lg">
						<div className="table-header-group bg-gray-50">
							<div className="table-row text-left text-sm font-semibold text-gray-900">
								{headerItems.map((item, i) => (
									<Fragment key={i}>
										{renderHeaderItem(item)}
									</Fragment>
								))}
							</div>
						</div>

						<div className="table-row-group divide-y divide-gray-200 bg-white">
							{tableData.map((post, i) => {
								return (
									<Fragment key={post.id}>
										<TableRow
											post={post}
											headerItems={headerItems}
											rowIndex={i}
											updatePost={updatePost}
										/>
									</Fragment>
								);
							})}
						</div>
					</div>
				</Panel>
			)}
		</>
	);
};

export default Table;
