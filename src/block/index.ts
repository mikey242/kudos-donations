import { BlockConfiguration, registerBlockType } from '@wordpress/blocks';
import save from './save';
import * as Components from './components';
import * as Controls from './controls';
import * as Contexts from './contexts';
import { KudosLogo } from './components';
import metadata from './block.json';
import { Edit } from './form/Edit';

window.kudos.front.api = { Components, Controls, Contexts };

export interface KudosButtonAttributes {
	button_label: string;
	type: 'form' | 'button';
	alignment: 'left' | 'center' | 'right';
	campaign_id?: string;
	[key: string]: unknown;
}

/**
 * Register block.
 */
registerBlockType(metadata as BlockConfiguration, {
	icon: KudosLogo,
	edit: Edit,
	save,
});
