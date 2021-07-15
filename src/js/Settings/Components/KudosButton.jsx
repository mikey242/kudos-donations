import {KudosLogo} from "./KudosLogo"

const KudosButton = ({children, className, color}) => {

    const {Button} = wp.components

    const classes = color ? '' : 'kd-bg-primary hover:kd-bg-primary-dark'

    return (
        <div
            id={'kudos-donations-' + Math.random().toString(36).substr(2, 9)}
            className={"kudos-donations kd-style-ignore" + className}
        >
            <Button
                className={'kd-transition kd-ease-in-out kd-font-sans focus:kd-ring-primary focus:kd-ring focus:kd-ring-offset-2 focus:kd-outline-none kd-text-center kd-text-white kd-leading-normal kd-font-normal kd-normal-case kd-no-underline kd-w-auto kd-h-auto kd-inline-flex kd-items-center kd-select-none kd-py-3 kd-px-5 kd-rounded-lg kd-cursor-pointer kd-shadow-none kd-border-none kd-bg-primary hover:kd-bg-primary-dark kd-logo-animate kudos-button-donate ' + classes}
                style={{backgroundColor: color}}
            >
                <div className="kd-mr-3 kd-flex kd-text-white">
                    <KudosLogo
                        lineColor="currentColor"
                        heartColor="currentColor"
                    />
                </div>
                {children}
            </Button>
        </div>
    )

}

export {KudosButton}
