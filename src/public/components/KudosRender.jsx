import React from 'react'
import PropTypes from 'prop-types'
import ReactShadowRoot from 'react-shadow-root'

const stylesheet = document.getElementById('kudos-donations-public-css')

KudosRender.propTypes = {
  children: PropTypes.node,
  themeColor: PropTypes.string
}

function KudosRender ({ children, themeColor }) {
  const style = `:host { 
      all: initial;
  }`

  return (
        <ReactShadowRoot>
            <link rel="stylesheet" href={stylesheet.href}/>
            <style>{style}</style>
            <style>{`:host {--kudos-theme-primary: ${themeColor}`}</style>
            <div id="kudos" className="font-sans text-base">
                {children}
            </div>
        </ReactShadowRoot>
  )
}

export default KudosRender
