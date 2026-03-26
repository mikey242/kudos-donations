import type { BlockConfiguration } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import save from './save';
import * as Components from './components';
import * as Controls from './controls';
import * as Contexts from './contexts';
import { KudosLogo } from './components';
import metadata from './block.json';
import { Edit, KudosButtonAttributes } from './form';

window.kudos.front = { Components, Controls, Contexts };

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
