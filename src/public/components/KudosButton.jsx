import { KudosLogo } from './KudosLogo'
import React from 'react'
import { Button } from '../../common/components/controls'

const KudosButton = ({ children, className, onClick = null }) => {
  return (
        <div
            id={'kudos-button'}
            className={className}
        >
            <Button
                onClick={() => onClick && onClick()}
            >
                <div className="mr-3 flex text-white">
                    <KudosLogo
                        className="w-5 h-5"
                        lineColor="currentColor"
                        heartColor="currentColor"
                    />
                </div>
                {children}
            </Button>
        </div>
  )
}

export { KudosButton }
