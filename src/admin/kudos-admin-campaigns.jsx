import { render } from '@wordpress/element';
import React from 'react';
import { KudosCampaigns } from './components/campaigns/KudosCampaigns';

const container = document.getElementById('kudos-settings');
const stylesheet = document.getElementById('kudos-donations-settings-css');
render(
	<KudosCampaigns container={container} stylesheet={stylesheet} />,
	container
);
