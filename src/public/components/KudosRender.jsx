import React from 'react'
import PropTypes from 'prop-types'
import ReactShadowRoot from 'react-shadow-root'

KudosRender.propTypes = {
  children: PropTypes.node,
  themeColor: PropTypes.string,
  stylesheet: PropTypes.string,
  style: PropTypes.string
}

function KudosRender ({ children, themeColor, stylesheet, style }) {
  return (
        <ReactShadowRoot>
            <link rel="stylesheet" href={stylesheet}/>
            {style && <style>{style}</style>}
            {themeColor && <style>{`:host {--kudos-theme-primary: ${themeColor}`}</style>}
            <div id="kudos" className="font-sans">
                {children}
            </div>
        </ReactShadowRoot>
  )
}

export default KudosRender
