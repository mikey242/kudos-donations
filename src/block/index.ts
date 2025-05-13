import { registerBlockType } from '@wordpress/blocks';
import save from './save';
import { KudosLogo } from './components/KudosLogo';
import metadata from './block.json';
import Edit, { KudosButtonAttributes } from './components/Edit';

/**
 * Register block.
 */
registerBlockType<KudosButtonAttributes>(metadata.name, {
	title: metadata.title,
	description: metadata.description,
	category: metadata.category,
	attributes: metadata.attributes,
	icon: KudosLogo,
	edit: Edit,
	save,
});
