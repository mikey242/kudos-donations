import type { BlockConfiguration } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import save from './save';
import { KudosLogo } from './components';
import metadata from './block.json';
import Edit, { KudosButtonAttributes } from './components/Edit';

/**
 * Register block.
 */
registerBlockType<KudosButtonAttributes>(
	metadata as BlockConfiguration<KudosButtonAttributes>,
	{
		icon: KudosLogo,
		edit: Edit,
		save,
	}
);
