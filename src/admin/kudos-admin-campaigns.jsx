import { render } from '@wordpress/element';
import React from 'react';
import { KudosCampaigns } from './campaigns/KudosCampaigns';

const stylesheet = document.getElementById('kudos-donations-settings-css');
render(
	<KudosCampaigns stylesheet={stylesheet} />,
	document.getElementById('kudos-settings')
);
