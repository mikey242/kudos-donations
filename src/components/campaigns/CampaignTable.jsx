import React from 'react';
import Panel from '../Panel';
import { __ } from '@wordpress/i18n';
import CampaignsTableRow from './CampaignsTableRow';

const CampaignTable = ({
	campaigns,
	editClick,
	duplicateClick,
	deleteClick,
	updateCampaign,
}) => {
	return (
		<Panel
			className="overflow-x-auto"
			title={__('Your campaigns', 'kudos-donations')}
		>
			<div className="table border-collapse min-w-full text-left sm:rounded-lg">
				<div className="table-header-group bg-gray-50">
					<div className="table-row text-left text-sm font-semibold text-gray-900">
						<div className="table-cell px-3 py-3.5">
							<span>
								{__('Campaign name', 'kudos-donations')}{' '}
							</span>
						</div>
						<div className="table-cell px-3 py-3.5">
							<span>{__('Color', 'kudos-donations')} </span>
						</div>
						<div className="table-cell px-3 py-3.5">
							<span>{__('Goal', 'kudos-donations')} </span>
						</div>
						<div className="table-cell px-3 py-3.5">
							<span>{__('Progress', 'kudos-donations')} </span>
						</div>
						<div className="table-cell px-3 py-3.5">
							<span>{__('Created', 'kudos-donations')} </span>
						</div>
						<div className="table-cell relative py-3.5 pl-3 pr-4 sm:pr-6">
							<span className="sr-only">
								{__('Edit', 'kudos-donations')}
							</span>
						</div>
					</div>
				</div>
				<div className="table-row-group divide-y divide-gray-200 bg-white">
					{campaigns?.map((campaign) => (
						<CampaignsTableRow
							key={campaign.id}
							updateCampaign={updateCampaign}
							deleteClick={deleteClick}
							duplicateClick={duplicateClick}
							editClick={editClick}
							campaign={campaign}
						/>
					))}
				</div>
			</div>
		</Panel>
	);
};

export default CampaignTable;
