import { KudosLogo } from './KudosLogo'

const KudosButton = ({ children, color, className, onClick = null }) => {
  console.log(color)
  return (
        <div
            id={'kudos-button'}
            className={className}
        >
            <div
                className={` ${!color && 'bg-primary '}ease-in-out transition font-sans focus:ring-primary focus:ring focus:ring-offset-2 focus:outline-none text-center text-white leading-normal normal-case no-underline w-auto h-auto inline-flex items-center select-none py-3 px-5 rounded-lg cursor-pointer shadow-none border-none hover:saturate-200 logo-animate`}
                onClick={() => onClick && onClick()}
                style={{ backgroundColor: color }}
            >
                <div className="mr-3 flex text-white">
                    <KudosLogo
                        lineColor="currentColor"
                        heartColor="currentColor"
                    />
                </div>
                {children}
            </div>
        </div>
  )
}

export { KudosButton }
