import {KudosLogo} from "./KudosLogo"

const {Button} = wp.components

const KudosButton = ({children, className, color}) => {

    const classes = color ? '' : 'kd-bg-primary hover:kd-bg-primary-dark'

    return (
        <div
            className={"kudos-donations " + className}
        >
            <Button
                className={'kd-transition kd-logo-animate kd-ease-in-out focus:kd-shadow-focus focus:kd-outline-none kd-font-sans kd-text-center kd-text-white kd-leading-normal kd-text-base kd-font-normal kd-normal-case kd-no-underline kd-w-auto kd-h-auto kd-inline-flex kd-items-center kd-select-none kd-py-3 kd-px-5 kd-rounded-lg kd-cursor-pointer kd-shadow-none kd-border-none ' + classes}
                style={{backgroundColor: color}}
            >
                <div className='kd-mr-3 kd-flex'>
                    <KudosLogo
                        lineColor='currentColor'
                        heartColor='currentColor'
                    />
                </div>
                {children}
            </Button>
        </div>
    )

}

export {KudosButton}