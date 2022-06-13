import { render } from '@wordpress/element';
import React from 'react';
import { KudosCampaigns } from './components/campaigns/KudosCampaigns';

const root = document.getElementById('kudos-settings');
const stylesheet = document.getElementById('kudos-donations-settings-css');
render(<KudosCampaigns root={root} stylesheet={stylesheet} />, root);
