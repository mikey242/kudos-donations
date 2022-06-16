import React from 'react'
import classNames from 'classnames'

const KudosLogo = ({
                       lineColor = '#2ec4b6',
                       heartColor = '#ff9f1c',
                       className,
                       style
                   }) => {
    return (
        <svg
            className={classNames(
                className,
                'logo origin-center duration-500 ease-in-out m-auto'
            )}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 555 449"
            style={style}
        >
            <path
                className="logo-line"
                fill={lineColor}
                d="M0,65.107C0,47.839 6.86,31.278 19.07,19.067C31.281,6.857 47.842,-0.003 65.11,-0.003L65.112,-0.003C101.202,-0.003 130.458,29.253 130.458,65.343L130.458,383.056C130.458,400.374 123.579,416.982 111.333,429.227C99.088,441.473 82.48,448.352 65.162,448.352L65.161,448.352C29.174,448.352 0.001,419.179 0.001,383.192C0.001,298.138 0,150.136 0,65.107Z"/>
            <path
                className="logo-heart origin-center duration-500 ease-in-out"
                fill={heartColor ?? lineColor}
                d="M489.887 224.178c78.407 47.195 78.407 141.59 39.201 188.784-39.2 47.194-117.612 47.194-196.019 0-58.809-33.04-117.612-117.992-156.818-188.784 39.206-70.793 98.01-155.744 156.818-188.781 78.407-47.196 156.818-47.196 196.02 0 39.205 47.195 39.205 141.587-39.202 188.781z"
            />
        </svg>
    )
}

export {KudosLogo}
