import { render } from '@wordpress/element';
import React from 'react';
import { KudosCampaigns } from './components/campaigns/KudosCampaigns';

const container = document.getElementById('kudos-settings');
render(<KudosCampaigns />, container);
