import { registerBlockType } from '@wordpress/blocks';
import Save from './save';
import Edit from './edit';
import React from 'react';
import { KudosLogo } from '../../common/components/KudosLogo';

/**
 * Register block.
 */
export default registerBlockType('iseardmedia/kudos-button', {
	icon: <KudosLogo />,
	edit: Edit,
	save: Save,
});
