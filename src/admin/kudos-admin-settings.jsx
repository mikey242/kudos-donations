import { render } from '@wordpress/element';
import React from 'react';
import KudosSettings from './components/settings/KudosSettings';

const root = document.getElementById('kudos-settings');
const stylesheet = document.getElementById('kudos-donations-settings-css');
render(<KudosSettings stylesheet={stylesheet} />, root);
