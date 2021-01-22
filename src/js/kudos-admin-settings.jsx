/**
 * WordPress dependencies
 */

const { render } = wp.element;

/**
 * Internal dependencies
 */
import {KudosAdmin} from './Settings/KudosAdmin'
// import '../scss/kudos-admin-settings.scss';

render( <KudosAdmin />, document.getElementById( 'kudos-settings' ) );
