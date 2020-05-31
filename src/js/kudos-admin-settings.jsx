/**
 * WordPress dependencies
 */

const {render} = wp.element;

/**
 * Internal dependencies
 */
import {KudosAdmin} from "./Admin/KudosAdmin";
import '../scss/kudos-admin-settings.scss';

render(
    <KudosAdmin/>,
    document.getElementById( 'kudos-settings' )
);