import React from 'react'
import classNames from 'classnames'

const KudosLogo = ({ lineColor = '#2ec4b6', heartColor = '#ff9f1c', className }) => {
  return (
        <svg
            className={classNames(
              className,
              'logo origin-center duration-500 ease-in-out m-auto'
            )}
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 555 449">
            <path className="logo-line" fill={lineColor}
                  d="M0-.003h130.458v448.355H.001z"/>
            <path
                className="logo-heart origin-center duration-500 ease-in-out"
                fill={heartColor ?? lineColor}
                d="M489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"/>
        </svg>
  )
}

export { KudosLogo }
