import { registerBlockType } from '@wordpress/blocks';
import save from './save';
import Edit from './edit';
import React from 'react';
import { KudosLogo } from './components/KudosLogo';
import metadata from './block.json';
import CampaignProvider from './contexts/CampaignContext';

/**
 * Register block.
 */
registerBlockType(metadata.name, {
	icon: <KudosLogo />,
	edit: (props) => (
		<CampaignProvider campaignId={props?.attributes?.campaign_id}>
			<Edit {...props} />
		</CampaignProvider>
	),
	save,
});
