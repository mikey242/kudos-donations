import React, { Fragment } from 'react';
import { Button, Flex, Spinner, VisuallyHidden } from '@wordpress/components';
import type { IconType } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import type { BaseEntity } from '../../../types/entity';
import { TableControls } from './TableControls';
import { useAdminQueryParams } from '../../hooks';
import { useEntitiesContext } from '../../contexts';
import { FilterContext } from './Filters';
import type { Filter } from './Filters';
import { Spacer } from '../Spacer';
import {
	arrowDown as arrowDownIcon,
	arrowUp as arrowUpIcon,
	chevronUpDown as chevronUpDownIcon,
} from '@wordpress/icons';

export interface Column<T extends BaseEntity = BaseEntity> {
	key: string;
	title: string;
	orderby?: string;
	render: (item: T) => React.ReactNode;
	align?: 'left' | 'right' | 'center';
	/**
	 * Fixed width for columns with short, predictable content (icons,
	 * dates, amounts). Columns without a size share the remaining space.
	 */
	size?: 'small' | 'medium';
}

export interface RowAction<T extends BaseEntity = BaseEntity> {
	id: string;
	label: string;
	icon: IconType;
	onClick?: (item: T) => void;
	href?: (item: T) => string | undefined;
	/** Return false to hide the action for this row. */
	isEligible?: (item: T) => boolean;
	isDisabled?: (item: T) => boolean;
	/** Replaces the default button, e.g. for a modal trigger. */
	render?: (item: T) => React.ReactNode;
}

/** Falsy entries are skipped, so an action can be included conditionally. */
export type RowActionList<T extends BaseEntity = BaseEntity> = Array<
	RowAction<T> | false | null | undefined
>;

interface TableProps<T extends BaseEntity> {
	columns: Column<T>[];
	actions?: RowActionList<T>;
	filters?: Filter[];
}

export const Table = <T extends BaseEntity>({
	columns,
	actions: actionList = [],
	filters,
}: TableProps<T>): React.ReactNode => {
	const { params, updateParams } = useAdminQueryParams();
	const { pluralName, hasResolved, entities, totalPages, totalItems } =
		useEntitiesContext<T>();
	const { order, orderby } = params;
	const hasRows = entities.length > 0;
	const actions = actionList.filter(Boolean) as RowAction<T>[];
	const hasActions = actions.length > 0;

	const sort = (column: string) => {
		const nextOrder =
			orderby === column && order === 'asc' ? 'desc' : 'asc';
		void updateParams({ order: nextOrder, orderby: column, paged: 1 });
	};

	const sortIcon = (column: string): IconType => {
		if (orderby !== column) {
			return chevronUpDownIcon;
		}
		return order === 'asc' ? arrowUpIcon : arrowDownIcon;
	};

	const ariaSort = (column?: string) => {
		if (!column || orderby !== column) {
			return undefined;
		}
		return order === 'asc' ? 'ascending' : 'descending';
	};

	let body: React.ReactNode;
	if (!hasRows) {
		body = (
			<tr>
				<td colSpan={columns.length + (hasActions ? 1 : 0)}>
					<Flex justify="center">
						{hasResolved ? (
							<p>
								{sprintf(
									// translators: %s is the plural post type name (e.g. Transactions)
									__('No %s', 'kudos-donations'),
									pluralName
								)}
							</p>
						) : (
							<Spinner style={{ margin: 0, padding: '1em' }} />
						)}
					</Flex>
				</td>
			</tr>
		);
	} else {
		body = entities.map((item) => (
			<tr key={item.id}>
				{columns.map((column) => (
					<td
						key={column.key}
						className="table-cell"
						style={{ textAlign: column.align ?? 'left' }}
					>
						{column.render(item)}
					</td>
				))}
				{hasActions && (
					<td className="table-cell">
						<RowActions item={item} actions={actions} />
					</td>
				)}
			</tr>
		));
	}

	return (
		<>
			<FilterContext filters={filters} />
			<TableControls
				filters={filters}
				totalPages={totalPages}
				totalItems={totalItems}
			/>
			<Spacer size={3} />
			<table
				className="widefat striped rounded"
				style={{ tableLayout: hasRows ? 'fixed' : 'auto' }}
			>
				<thead>
					<tr>
						{columns.map((column) => (
							<th
								key={column.key}
								scope="col"
								aria-sort={ariaSort(column.orderby)}
								className={
									column.size
										? `kudos-col-${column.size}`
										: undefined
								}
								style={{ textAlign: column.align ?? 'left' }}
							>
								{column.orderby ? (
									<Button
										onClick={() => sort(column.orderby)}
										icon={sortIcon(column.orderby)}
									>
										{column.title}
									</Button>
								) : (
									<span>{column.title}</span>
								)}
							</th>
						))}
						{hasActions && (
							<th
								scope="col"
								className="kudos-col-actions"
								style={
									{
										'--kudos-action-count': actions.length,
									} as React.CSSProperties
								}
							>
								<VisuallyHidden>
									{__('Actions', 'kudos-donations')}
								</VisuallyHidden>
							</th>
						)}
					</tr>
				</thead>
				<tbody className={hasResolved ? 'is-loaded' : 'is-loading'}>
					{body}
				</tbody>
			</table>
			<Spacer size={3} />
			<TableControls totalPages={totalPages} totalItems={totalItems} />
		</>
	);
};

interface RowActionsProps<T extends BaseEntity> {
	item: T;
	actions: RowAction<T>[];
}

const RowActions = <T extends BaseEntity>({
	item,
	actions,
}: RowActionsProps<T>): React.ReactNode => (
	<Flex justify="flex-end">
		{actions
			.filter((action) => action.isEligible?.(item) ?? true)
			.map((action) =>
				action.render ? (
					<Fragment key={action.id}>{action.render(item)}</Fragment>
				) : (
					<Button
						key={action.id}
						size="compact"
						icon={action.icon}
						label={action.label}
						href={action.href?.(item)}
						disabled={action.isDisabled?.(item)}
						onClick={
							action.onClick
								? () => action.onClick(item)
								: undefined
						}
					/>
				)
			)}
	</Flex>
);

/** Shared "Created" column, sortable by `created_at`. */
export const createdAtColumn = <T extends BaseEntity>(): Column<T> => ({
	key: 'created_at',
	title: __('Created', 'kudos-donations'),
	orderby: 'created_at',
	size: 'medium',
	align: 'right',
	render: (item) => (
		<time
			dateTime={item.created_at}
			title={dateI18n('d-m-Y H:i:s', item.created_at)}
			style={{ fontStyle: 'italic' }}
		>
			{dateI18n('d-m-Y', item.created_at)}
		</time>
	),
});
