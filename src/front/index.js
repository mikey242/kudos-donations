import { registerBlockType } from '@wordpress/blocks';
import save from './save';
import Edit from './edit';
import React from 'react';
import { KudosLogo } from './components/KudosLogo';
import metadata from './block.json';

/**
 * Register block.
 */
registerBlockType(metadata.name, {
	icon: <KudosLogo />,
	edit: Edit,
	save,
});
