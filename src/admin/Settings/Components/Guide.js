import classnames from 'classnames';
import { useState } from '@wordpress/element';
import { Button } from "@wordpress/components"
import { __ } from "@wordpress/i18n"
import {Btn} from "./Btn"

const Guide = ({pages = [], className, onFinish}) => {

    const [ currentPage, setCurrentPage ] = useState( 0 );
    const canGoBack = currentPage > 0;
    const canGoForward = currentPage < pages.length - 1;

    const goBack = () => {
        if ( canGoBack ) {
            setCurrentPage( currentPage - 1 );
        }
    };

    const goForward = () => {
        if ( canGoForward ) {
            setCurrentPage( currentPage + 1 );
        }
    };

    return (
        <div className={ classnames("intro kd-fixed kd-top-0 kd-left-0 kd-bottom-0 kd-right-0 kd-z-[100000] kd-w-screen kd-min-h-screen kd-bg-gray-100", className)}>
            <div className={"kd-h-full kd-flex kd-justify-center kd-items-center"}>
                <div className={"intro-content kd-rounded kd-bg-white kd-my-5 kd-flex kd-flex-col kd-justify-center kd-items-center kd-h-full kd-w-[768px]"}>
                    <div className="intro-image kd-w-full kd-text-center">
                        <img alt="Page graphic" className={"kd-w-full"} src={pages[ currentPage ].imageSrc}/>
                    </div>
                    <div className="intro-content kd-p-5 kd-w-3/4">
                        { pages[ currentPage ].content }
                    </div>
                    <div className="intro-nav kd-mt-auto kd-w-11/12 kd-mt-5 kd-mb-5 kd-flex kd-justify-between">
                        { canGoBack && (
                            <Btn
                                isSecondary
                                onClick={ goBack }
                            >
                                { __( 'Previous', 'kudos-donations' ) }
                            </Btn>
                        ) }
                        { canGoForward && (
                            <Btn
                                isSecondary
                                className={"kd-ml-auto"}
                                disabled={(pages[currentPage].nextDisabled ?? false)}
                                onClick={ goForward }
                            >
                                { pages[ currentPage ].hasOwnProperty('nextLabel') ? pages[ currentPage ].nextLabel : __( 'Next', 'kudos-donations' ) }
                            </Btn>
                        ) }
                        { ! canGoForward && (
                            <Button
                                onClick={ onFinish }
                            >
                                { __( 'Finish', 'kudos-donations' ) }
                            </Button>
                        ) }
                    </div>
                </div>
            </div>
        </div>
    )
}

export {Guide}
