/**
 * WordPress dependencies
 */

const {render} = wp.element

/**
 * Internal dependencies
 */
import {KudosAdmin} from './Settings/KudosAdmin'

render(<KudosAdmin/>, document.getElementById('kudos-settings'))
