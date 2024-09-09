import { registerBlockType } from '@wordpress/blocks';
import Save from './save';
import Edit from './edit';
import React from 'react';
import { KudosLogo } from '../components/KudosLogo';
import metadata from './block.json';

/**
 * Register block.
 */
export default registerBlockType(metadata, {
	icon: <KudosLogo />,
	edit: Edit,
	save: Save,
});
