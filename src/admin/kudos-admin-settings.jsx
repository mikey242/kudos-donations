import { render } from '@wordpress/element';
import React from 'react';
import KudosSettings from './components/settings/KudosSettings';
import '../images/logo-colour-40.png';

const root = document.getElementById('kudos-settings');
render(<KudosSettings />, root);
