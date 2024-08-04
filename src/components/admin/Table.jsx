import React from 'react';
import { Fragment, useRef } from '@wordpress/element';
import { Pane } from '../common/Panel';
import { __ } from '@wordpress/i18n';
import { ChevronUpDownIcon, ChevronUpIcon } from '@heroicons/react/20/solid';

import { StringParam, useQueryParams, withDefault } from 'use-query-params';
import { clsx } from 'clsx';
import { FormProvider, useForm } from 'react-hook-form';

export const Table = ({ headerItems, tableData, updatePost }) => {
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
				<>
					<h2 className="text-center my-5">
						{__('Your campaigns', 'kudos-donations')}
					</h2>
					<Pane>
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
					</Pane>
				</>
			)}
		</>
	);
};

const TableRow = ({ post, headerItems, rowIndex, updatePost }) => {
	const formRef = useRef(null);
	const methods = useForm({
		defaultValues: {
			...post,
			title: post?.title?.rendered,
		},
	});

	const save = (data) => {
		updatePost(data.id, data, false);
	};

	const { handleSubmit } = methods;
	return (
		<FormProvider {...methods}>
			<form
				className="table-row text-sm"
				onSubmit={handleSubmit(save)}
				ref={formRef}
			>
				{headerItems.map((column, i) => {
					return (
						<div
							key={column.title + post.id}
							className={clsx(
								headerItems[i]?.cellClass,
								'table-cell align-middle whitespace-nowrap px-3 py-4 text-gray-900'
							)}
						>
							{column.dataCallback &&
								column.dataCallback(rowIndex, formRef)}
						</div>
					);
				})}
			</form>
		</FormProvider>
	);
};
