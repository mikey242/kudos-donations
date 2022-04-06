/**
 * Kudos Render
 * @link https://stackoverflow.com/questions/42274721/shadow-dom-and-reactjs
 */

import { render, StrictMode } from '@wordpress/element'
import React from 'react'
import KudosRender from './components/KudosRender'

// Select the web components as target for render.
const roots = document.querySelectorAll('kudos-donations')
const stylesheet = document.getElementById('kudos-donations-public-css')

roots.forEach((root) => {
  const buttonLabel = root.getAttribute('label')
  const campaignId = root.getAttribute('campaign')
  render(<StrictMode>
        <KudosRender
            root={root}
            campaignId={campaignId}
            buttonLabel={buttonLabel}
            stylesheet={stylesheet}
        />
    </StrictMode>, root)
})
