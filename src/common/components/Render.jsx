import React from 'react'
import ReactShadowRoot from 'react-shadow-root'
import classNames from 'classnames'

function Render({children, themeColor, stylesheet, style, className}) {
    return (
        <ReactShadowRoot>
            <link rel="stylesheet" href={stylesheet}/>
            {style && <style>{style}</style>}
            {themeColor && (
                <style>{`:host {--kudos-theme-primary: ${themeColor}`}</style>
            )}
            <div id="kudos-container">
                <div id="kudos" className={classNames(className, 'font-sans')}>
                    {children}
                </div>
            </div>
        </ReactShadowRoot>
    )
}

export default Render
