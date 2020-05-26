/**
 * WordPress dependencies
 */

const {render} = wp.element;

/**
 * Internal dependencies
 */
import {KudosAdmin} from "./components/KudosAdmin";
import '../scss/kudos-admin-react.scss';

render(
    <KudosAdmin/>,
    document.getElementById( 'kudos-dashboard' )
);