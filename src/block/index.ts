import type { BlockType } from '@wordpress/blocks';
import { registerBlockType } from '@wordpress/blocks';
import save from './save';
import * as Components from './components';
import * as Controls from './controls';
import * as Contexts from './contexts';
import { KudosLogo } from './components';
import metadata from './block.json';
import { Edit } from './form/Edit';

window.kudos.front.api = { Components, Controls, Contexts };

/**
 * Register block.
 */
registerBlockType(
	metadata as Record<string, unknown>,
	{ icon: KudosLogo, edit: Edit, save } as unknown as Partial<BlockType>
);
