import { FormProvider, useForm } from 'react-hook-form';
import React from 'react';
import { useRef } from '@wordpress/element';
import classNames from 'classnames';

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
							className={classNames(
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

export default TableRow;
