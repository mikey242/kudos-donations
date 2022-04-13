/**
 * Kudos Render
 * @link https://stackoverflow.com/questions/42274721/shadow-dom-and-reactjs
 */

import { render } from '@wordpress/element'
import React from 'react'
import KudosDonate from './components/KudosDonate'
import KudosMessage from './components/KudosMessage'

// Select the web components as target for render.
const roots = document.querySelectorAll('.kudos-form')

// Kudos Donations form/modal
roots.forEach((root) => {
  const buttonLabel = root.dataset.label
  const campaignId = root.dataset.campaign
  render(
        <KudosDonate
            root={root}
            campaignId={campaignId}
            buttonLabel={buttonLabel}
        />
        , root)
})

const messages = document.querySelectorAll('.kudos-message')
// Kudos Donations message
messages.forEach((message) => {
  const title = message.dataset.title
  const body = message.dataset.body
  const campaignId = message.dataset.campaign
  render(
        <KudosMessage
            root={message}
            campaignId={campaignId}
            title={title}
            body={body}
        />
        , message)
})
