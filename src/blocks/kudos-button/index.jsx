import {registerBlockType} from '@wordpress/blocks'
import Save from "./save"
import Edit from "./edit"
import logo from '../../images/logo-colour.svg'

/**
 * Register block.
 */
export default registerBlockType('iseardmedia/kudos-button', {
    icon: <img width="30" src={logo} alt="Kudos Logo"/>,
    edit: Edit,
    save: Save
})
