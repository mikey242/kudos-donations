/**
 * Kudos Render
 * @link https://stackoverflow.com/questions/42274721/shadow-dom-and-reactjs
 */

import {render} from "@wordpress/element"
import React from 'react'
import KudosRender from "../common/components/KudosRender"

// Select the web component as target for component.
const root = document.querySelector('kudos-donations');

render(<KudosRender label={root.getAttribute('label')}/>, root)
