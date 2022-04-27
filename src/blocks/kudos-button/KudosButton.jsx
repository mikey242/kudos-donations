import {KudosLogo} from "../../common/components/KudosLogo"

const KudosButton = ({children, className, color}) => {

    return (
        <div
            id={'kudos-donations-' + Math.random().toString(36).substr(2, 9)}
            className={"kudos-donations kd-style-ignore" + className}
        >
            <div
                className={'kd-transition kd-ease-in-out kd-font-sans focus:kd-ring-primary focus:kd-ring focus:kd-ring-offset-2 focus:kd-outline-none kd-text-center kd-text-white kd-leading-normal kd-normal-case kd-no-underline kd-w-auto kd-h-auto kd-inline-flex kd-items-center kd-select-none kd-py-3 kd-px-5 kd-rounded-lg kd-cursor-pointer kd-shadow-none kd-border-none kd-bg-primary hover:kd-bg-primary-dark kd-logo-animate kudos-button-donate'}
                style={{backgroundColor: color}}
            >
                <div className="kd-mr-3 kd-flex kd-text-white">
                    <KudosLogo
                        lineColor="currentColor"
                        heartColor="currentColor"
                        className="kd-w-5 kd-h-5"
                    />
                </div>
                {children}
            </div>
        </div>
    )

}

export {KudosButton}
