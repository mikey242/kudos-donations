import classnames from 'classnames'
import {useState} from '@wordpress/element'
import {useFocusOnMount, useMergeRefs, useConstrainedTabbing} from "@wordpress/compose"
import {LEFT, RIGHT, ESCAPE} from '@wordpress/keycodes'
import {Button} from "@wordpress/components"
import {__} from "@wordpress/i18n"
import {Btn} from "./Btn"

const Guide = ({pages = [], className, onFinish}) => {

    const [currentPage, setCurrentPage] = useState(0)
    const canGoBack = currentPage > 0
    const canGoForward = currentPage < pages.length - 1
    const focusOnMountRef = useFocusOnMount(true)
    const constrainedTabbingRef = useConstrainedTabbing()

    const handleKeyPress = (event) => {
        if (event.keyCode === LEFT) {
            goBack()
        }
        if (event.keyCode === RIGHT) {
            goForward()
        }
        if (event.keyCode === ESCAPE) {
            onFinish()
        }
    }

    const goBack = () => {
        if (canGoBack) {
            setCurrentPage(currentPage - 1)
        }
    }

    const goForward = () => {
        if (canGoForward) {
            setCurrentPage(currentPage + 1)
        }
    }

    const pageNav = pages.map((page, i) => {
        const current = currentPage === i ? 'kd-bg-orange-500' : 'kd-bg-transparent'
        return (
            <li
                className={"kd-border kd-cursor-pointer kd-border-solid kd-border-orange-500 kd-m-0 kd-mx-2 kd-rounded-full kd-w-2 kd-h-2 " + current}
                key={i}
                onClick={() => setCurrentPage(i)}
            >
            </li>
        )
    })

    return (
        <div
            ref={useMergeRefs([
                focusOnMountRef, constrainedTabbingRef
            ])}
            tabIndex="-1"
            onKeyDown={(e) => handleKeyPress(e)}
            className={classnames("intro kd-text-2xl kd-leading-6 kd-fixed kd-top-0 kd-left-0 kd-bottom-0 kd-right-0 kd-z-[100000] kd-w-screen kd-min-h-screen kd-bg-green-500", className)}>
            <div className={"kd-h-full kd-flex kd-justify-center kd-items-center kd-overflow-auto"}>
                <div
                    className={"intro-content kd-bg-gray-50 kd-flex kd-flex-col kd-justify-center kd-items-center kd-h-full kd-w-[768px]"}>
                    <small
                        className={"kd-ml-auto kd-mr-3 kd-mt-3 kd-cursor-pointer kd-text-gray-500 kd-underline"}
                        onClick={onFinish}
                    >
                        {__('skip', 'kudos-donations')}
                    </small>
                    <div className="intro-content kd-m-auto kd-w-3/4">
                        <div className="intro-image kd-w-full">
                            <img alt="Page graphic" className={"kd-w-full"} src={pages[currentPage].imageSrc}/>
                        </div>
                        <h1 className={"kd-leading-normal kd-text-center"}>{pages[currentPage].heading}</h1>
                        {pages[currentPage].content}
                    </div>
                    <div
                        className="intro-nav kd-py-3 kd-border-0 kd-border-t kd-border-solid kd-border-gray-200 kd-flex kd-justify-between kd-items-center kd-w-11/12 kd-mt-5 kd-mb-5">
                        <Btn
                            className={canGoBack ? "kd-visible" : "kd-invisible"}
                            onClick={goBack}
                        >
                            {__('Previous', 'kudos-donations')}
                        </Btn>

                        <ul className={"kd-flex kd-justify-center kd-m-0"}>
                            {pageNav}
                        </ul>
                        {canGoForward && (
                            <Btn
                                disabled={(pages[currentPage].nextDisabled ?? false)}
                                onClick={goForward}
                            >
                                {pages[currentPage].hasOwnProperty('nextLabel') ? pages[currentPage].nextLabel : __('Next', 'kudos-donations')}
                            </Btn>
                        )}
                        {!canGoForward && (
                            <Button
                                isPrimary
                                onClick={onFinish}
                            >
                                {__('Finish', 'kudos-donations')}
                            </Button>
                        )}

                    </div>
                </div>
            </div>
        </div>
    )
}

export {Guide}
