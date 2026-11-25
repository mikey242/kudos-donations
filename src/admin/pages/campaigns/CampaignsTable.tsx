import {
	Button,
	ColorIndicator,
	Fill,
	Flex,
	ProgressBar,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import React, { ReactNode } from 'react';
import { copy as copyIcon, plus as plusIcon } from '@wordpress/icons';
import { createdAtColumn, Table } from '../../components';
import type { Column, RowActionList } from '../../components';
import { useRowActions } from '../use-row-actions';
import { useEntitiesContext } from '../../contexts';
import { useAdminQueryParams } from '../../hooks';
import { SLOT_HEADER_ACTIONS } from '../../slot-names';
import type { Campaign } from '../../../types/entity';
import { formatAmount } from '../../../utils/currency';

export const CampaignsTable = ({ handleNew }): ReactNode => {
	const { updateParams } = useAdminQueryParams();
	const { handleDuplicate } = useEntitiesContext<Campaign>();
	const rowActions = useRowActions<Campaign>();

	const columns: Column<Campaign>[] = [
		{
			key: 'title',
			title: __('Campaign', 'kudos-donations'),
			orderby: 'title',
			render: (campaign) => (
				<Button
					showTooltip={true}
					style={{ fontWeight: 'bold', color: 'inherit' }}
					label={sprintf(
						/* translators: %s is the campaign name */
						__('Edit %s', 'kudos-donations'),
						campaign.title
					)}
					variant="link"
					onClick={() => void updateParams({ entity: campaign.id })}
				>
					{campaign.title}
				</Button>
			),
		},
		{
			key: 'theme_color',
			title: __('Theme color', 'kudos-donations'),
			size: 'small',
			render: (campaign) => (
				<ColorIndicator colorValue={campaign.theme_color} />
			),
		},
		{
			key: 'raised',
			title: __('Raised', 'kudos-donations'),
			render: (campaign) => {
				const total = Number(campaign.total) || 0;
				const goal = Number(campaign.goal) || 0;
				const raised = formatAmount(total, campaign.currency);

				if (!goal) {
					return raised;
				}

				const progress = Math.round((total / goal) * 100);

				return (
					<>
						<Flex justify="space-between">
							<span>
								{sprintf(
									/* translators: %1$s is the amount raised, %2$s is the campaign goal */
									__('%1$s of %2$s', 'kudos-donations'),
									raised,
									formatAmount(goal, campaign.currency)
								)}
							</span>
							<span className="kudos-campaign-progress-percent">
								{`${progress}%`}
							</span>
						</Flex>
						<ProgressBar
							className="kudos-campaign-progress"
							value={progress}
						/>
					</>
				);
			},
		},
		createdAtColumn(),
	];

	const actions: RowActionList<Campaign> = [
		rowActions.viewTransactions('campaign_id'),
		rowActions.edit(__('Edit campaign', 'kudos-donations')),
		{
			id: 'duplicate',
			label: __('Duplicate campaign', 'kudos-donations'),
			icon: copyIcon,
			onClick: (campaign) => void handleDuplicate(campaign),
		},
		rowActions.remove(
			__('Delete campaign', 'kudos-donations'),
			__(
				'Are you sure you wish to delete this campaign?',
				'kudos-donations'
			)
		),
	];

	return (
		<>
			<Fill name={SLOT_HEADER_ACTIONS}>
				<Button
					variant="primary"
					onClick={handleNew}
					text={__('New campaign', 'kudos-donations')}
					icon={plusIcon}
				/>
			</Fill>
			<Table columns={columns} actions={actions} />
		</>
	);
};
