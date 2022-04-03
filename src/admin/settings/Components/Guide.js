import classnames from 'classnames'
import {useState} from '@wordpress/element'
import {useFocusOnMount, useMergeRefs, useConstrainedTabbing} from "@wordpress/compose"
import {LEFT, RIGHT, ESCAPE} from '@wordpress/keycodes'
import {Button} from "@wordpress/components"
import {__} from "@wordpress/i18n"
import {Btn} from "./Btn"

const Guide = ({pages = [], className, onFinish}) => {

    const [currentPage, setCurrentPage] = useState(0)
    const [furthestPage, setFurthestPage] = useState(0)
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
            setFurthestPage(Math.max(currentPage + 1, furthestPage))
            setCurrentPage(currentPage + 1)
        }
    }

    const pageNav = pages.map((page, i) => {
        const isAccessible = furthestPage >= i
        const currentClass = currentPage === i ? "bg-orange-500" : "bg-transparent"
        const accessibleClass = isAccessible ? "cursor-pointer border-orange-500" : "border-orange-200"
        const classes = classnames(currentClass, accessibleClass)
        return (
            <li
                className={classnames(classes, "border border-solid m-0 mx-2 rounded-full w-2 h-2")}
                key={i}
                onClick={() => isAccessible ? setCurrentPage(i) : null}
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
            className={classnames("intro text-2xl leading-6 fixed top-0 left-0 bottom-0 right-0 z-[100000] w-screen min-h-screen bg-green-500", className)}>
            <div className={"h-full flex justify-center items-center overflow-auto"}>
                <div
                    className={"intro-content bg-gray-50 flex flex-col justify-center items-center h-full w-[768px]"}>
                    <small
                        className={"ml-auto mr-3 mt-3 cursor-pointer text-gray-500 underline"}
                        onClick={onFinish}
                    >
                        {__('skip', 'kudos-donations')}
                    </small>
                    <div className="intro-content m-auto w-3/4">
                        <div className="intro-image w-full">
                            <img alt="Page graphic" className={"w-full"} src={pages[currentPage].imageSrc}/>
                        </div>
                        <h1 className={"leading-normal text-center"}>{pages[currentPage].heading}</h1>
                        {pages[currentPage].content}
                    </div>
                    <div
                        className="intro-nav py-3 border-0 border-t border-solid border-gray-200 flex justify-between items-center w-11/12 mt-5 mb-5">
                        <Btn
                            className={canGoBack ? "visible" : "invisible"}
                            onClick={goBack}
                        >
                            {__('Previous', 'kudos-donations')}
                        </Btn>

                        <ul className={"flex justify-center m-0"}>
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
