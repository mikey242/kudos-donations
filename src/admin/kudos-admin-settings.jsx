/**
 * WordPress dependencies
 */

import {render} from "@wordpress/element"

/**
 * Internal dependencies
 */
import {KudosAdmin} from './Settings/KudosAdmin'

render(<KudosAdmin/>, document.getElementById('kudos-settings'))
