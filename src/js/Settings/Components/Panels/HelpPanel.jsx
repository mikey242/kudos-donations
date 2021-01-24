import logo from '../../../../img/full-logo-green.svg'

const {__} = wp.i18n
const {
    PanelBody,
    Button,
    Icon
} = wp.components

const HelpPanel = ({updateSetting}) => {

    const door = () => (
        <Icon
            size="16"
            icon={
                <svg xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 640 512">
                    <path fill="currentColor"
                          d="M624 448h-80V113.45C544 86.19 522.47 64 496 64H384v64h96v384h144c8.84 0 16-7.16 16-16v-32c0-8.84-7.16-16-16-16zM312.24 1.01l-192 49.74C105.99 54.44 96 67.7 96 82.92V448H16c-8.84 0-16 7.16-16 16v32c0 8.84 7.16 16 16 16h336V33.18c0-21.58-19.56-37.41-39.76-32.17zM264 288c-13.25 0-24-14.33-24-32s10.75-32 24-32 24 14.33 24 32-10.75 32-24 32z"/>
                </svg>
            }
        />
    )

    const question = () => (
        <Icon
            size="16"
            icon={
                <svg xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 512 512">
                    <path fill="currentColor"
                          d="M504 256c0 136.997-111.043 248-248 248S8 392.997 8 256C8 119.083 119.043 8 256 8s248 111.083 248 248zM262.655 90c-54.497 0-89.255 22.957-116.549 63.758-3.536 5.286-2.353 12.415 2.715 16.258l34.699 26.31c5.205 3.947 12.621 3.008 16.665-2.122 17.864-22.658 30.113-35.797 57.303-35.797 20.429 0 45.698 13.148 45.698 32.958 0 14.976-12.363 22.667-32.534 33.976C247.128 238.528 216 254.941 216 296v4c0 6.627 5.373 12 12 12h56c6.627 0 12-5.373 12-12v-1.333c0-28.462 83.186-29.647 83.186-106.667 0-58.002-60.165-102-116.531-102zM256 338c-25.365 0-46 20.635-46 46 0 25.364 20.635 46 46 46s46-20.636 46-46c0-25.365-20.635-46-46-46z"/>
                </svg>
            }
        />
    )

    const quill = () => (
        <Icon
            size="16"
            icon={
                <svg xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 512 512">
                    <path fill="currentColor"
                          d="M467.14 44.84c-62.55-62.48-161.67-64.78-252.28 25.73-78.61 78.52-60.98 60.92-85.75 85.66-60.46 60.39-70.39 150.83-63.64 211.17l178.44-178.25c6.26-6.25 16.4-6.25 22.65 0s6.25 16.38 0 22.63L7.04 471.03c-9.38 9.37-9.38 24.57 0 33.94 9.38 9.37 24.6 9.37 33.98 0l66.1-66.03C159.42 454.65 279 457.11 353.95 384h-98.19l147.57-49.14c49.99-49.93 36.38-36.18 46.31-46.86h-97.78l131.54-43.8c45.44-74.46 34.31-148.84-16.26-199.36z"/>
                </svg>
            }
        />
    )

    return (
        <PanelBody>
            <h2>{__('Need some assistance?', 'kudos-donations')}</h2>

            <p>{__("Don't hesitate to get in touch if you need any help or have a suggestion. ", 'kudos-donations')}</p>

            <div className="kd-flex">
                <div className="kd-flex-grow">
                    <Button
                        isSecondary
                        className={"kd-mr-2"}
                        icon={door}
                        onClick={() => {
                            updateSetting('_kudos_show_intro', true)
                        }}
                    >
                        {__('Show welcome guide', 'kudos-donations')}
                    </Button>
                    <Button
                        isSecondary
                        className={"kd-mr-2"}
                        target="_blank"
                        href="https://kudosdonations.com/faq/"
                        icon={question}
                    >
                        {__('Visit our F.A.Q', 'kudos-donations')}
                    </Button>
                    <Button
                        isSecondary
                        href="https://wordpress.org/support/plugin/kudos-donations/reviews/#new-post"
                        target="_blank"
                        icon={quill}
                    >
                        {__('Leave a review', 'kudos-donations')}
                    </Button>
                </div>
                <div>
                    <img width="140" src={logo} className="kd-mr-4" alt="Kudos Logo"/>
                </div>
            </div>
        </PanelBody>
    )
}

export {HelpPanel}
