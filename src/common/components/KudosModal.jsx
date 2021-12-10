import logo from "../../images/logo-colour.svg"
import {__} from "@wordpress/i18n"
import classNames from 'classnames'

const KudosModal = (props) => {

    return (
        <>
            <div id="kudos-modal"
                 className={classNames((!props.isOpen && !props.isClosing ) && "hidden", "kudos-modal absolute z-1050 text-[16px] xl:text-[18px] 2xl:text-[18px]")}
                 role="dialog" aria-hidden={!props.isOpen} aria-modal="true"
                 aria-label={__("Kudos Modal")}>
                <div
                    className="kudos-modal-overlay flex justify-center items-center fixed top-0 left-0 w-full h-full bg-[#1a202ccc]">
                    <div data-page="1"
                         className="kudos-modal-container bg-white p-8 xs:rounded-lg w-full h-full xs:h-auto lg:w-2/4 max-w-lg relative overflow-auto xs:overflow-hidden origin-right
                    before:w-full before:h-full before:bg-white before:top-0 before:left-0 before:absolute before:-z-1 before:opacity-70"
                         tabIndex="-1">
                        <div className="kudos-modal-header flex items-center justify-between">
                    <span className="mr-3 inline-block flex" title="Kudos Donations">
                        <img alt="Kudos logo" className="h-6" src={logo}/>
                    </span>
                            {props.header}
                            <span
                                className="focus:ring hover:text-primary-dark xs:inline transition-shadow ease-in-out ring-primary ring-offset-2 rounded-full w-5 h-5 cursor-pointer text-center self-center"
                                onClick={props.toggle}
                                tabIndex="0 "
                                title={__('Close modal', 'kudos-donations')}
                            >X</span>
                        </div>
                        <div className="mt-4 block">
                            {props.children}
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
}

export {KudosModal}