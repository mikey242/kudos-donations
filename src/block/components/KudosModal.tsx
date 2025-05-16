// @see https://medium.com/cstech/achieving-focus-trapping-in-a-react-modal-component-3f28f596f35b
// @see https://dev.to/open-wc/mind-the-document-activeelement-2o9a
import { __ } from '@wordpress/i18n';
import logo from '../../../assets/images/logo-colour.svg';
import { Transition } from '@headlessui/react';
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { XMarkIcon } from '@heroicons/react/24/outline';
import React, { ReactNode } from 'react';

interface KudosModalProps {
	isOpen?: boolean;
	toggleModal: () => void;
	children: ReactNode;
	showLogo?: boolean;
}

export const KudosModal = ({
	isOpen = false,
	toggleModal,
	children,
	showLogo = true,
}: KudosModalProps) => {
	const targetRef = useRef<HTMLDivElement | null>(null);
	const [firstElement, setFirstElement] = useState<HTMLElement | null>(null);
	const [lastElement, setLastElement] = useState<HTMLElement | null>(null);

	const toggle = useCallback(() => {
		if (typeof toggleModal === 'function') {
			toggleModal();
		}
	}, [toggleModal]);

	const setUp = useCallback(() => {
		const focusableElements =
			targetRef.current?.querySelectorAll<HTMLElement>(
				'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
			);
		setFirstElement(focusableElements ? focusableElements[0] : null);
		setLastElement(
			focusableElements
				? focusableElements[focusableElements.length - 1]
				: null
		);
		const initialFocus = targetRef.current?.querySelector<HTMLElement>(
			'[name*="value"]:not([type="hidden"])'
		);
		initialFocus?.focus();
	}, []);

	const handleKeyPress = useCallback(
		(e: KeyboardEvent) => {
			if (e.key === 'Escape') {
				toggle();
			}
			if (e.key === 'Tab') {
				const activeElement = (
					targetRef.current.getRootNode() as Document
				).activeElement as HTMLElement;
				if (e.shiftKey && activeElement === firstElement) {
					e.preventDefault();
					lastElement.focus();
				} else if (!e.shiftKey && activeElement === lastElement) {
					e.preventDefault();
					firstElement.focus();
				}
			}
		},
		[firstElement, lastElement, toggle]
	);

	useEffect(() => {
		if (isOpen) {
			setUp();
			document.documentElement.style.setProperty(
				'--kudos-modal-overflow',
				'hidden'
			);
			document.addEventListener('keydown', handleKeyPress, false);
			return () => {
				document.documentElement.style.setProperty(
					'--kudos-modal-overflow',
					'auto'
				);
				document.removeEventListener('keydown', handleKeyPress, false);
			};
		}
	}, [isOpen, handleKeyPress, setUp]);

	return (
		<div id="modal-container" ref={targetRef}>
			<Transition show={isOpen}>
				<div className="fixed z-[999999] inset-0 overflow-y-scroll">
					<>
						<Transition.Child
							enter="transition-opacity duration-[400ms]"
							enterFrom="opacity-0"
							enterTo="opacity-100"
							leave="transition-opacity duration-200 ease-in-out"
							leaveFrom="opacity-100"
							leaveTo="opacity-0"
						>
							<div
								id="modal-overlay"
								className="fixed inset-0 bg-black/30"
								aria-hidden="true"
							/>
						</Transition.Child>
						<Transition.Child
							beforeEnter={setUp}
							enter="transition-all duration-[400ms]"
							enterFrom="opacity-0 rotate-[-5deg] translate-x-3 translate-y-3 scale-90"
							enterTo="opacity-100 rotate-0 translate-x-0 translate-y-0 scale-100"
							leave="transition-all duration-200"
							leaveFrom="opacity-100 rotate-0 translate-y-0 scale-100"
							leaveTo="opacity-0 translate-y-3 scale-90"
						>
							<div className="flex p-0 pt-4 xs:px-4 xs:pb-20 text-center xs:block sm:p-0">
								{/* This element is to trick the browser into centering the modal contents. */}
								<span
									className="hidden sm:inline-block sm:align-middle sm:h-screen"
									aria-hidden="true"
								>
									&#8203;
								</span>
								<div
									id="modal"
									className="inline-block bg-white rounded-t-lg xs:rounded-lg text-left overflow-hidden shadow-xl transform transition-all duration-200 sm:align-middle sm:max-w-lg w-full"
								>
									<div className="bg-white p-8">
										<div
											id="modal-header"
											className="flex items-center justify-between"
										>
											{showLogo && (
												<span
													className="mr-3 flex"
													title="Kudos Donations"
												>
													<img
														alt="Kudos logo"
														className="h-6"
														src={logo}
													/>
												</span>
											)}
											<button
												className="bg-transparent transition p-0 inline leading-none border-0 focus:outline-none focus:ring hover:text-primary-dark ring-primary ring-offset-2 rounded-full w-5 h-5 cursor-pointer text-center ml-auto"
												onClick={toggle}
												type="button"
												title={__(
													'Close modal',
													'kudos-donations'
												)}
											>
												<XMarkIcon className="align-middle w-5 h-5" />
											</button>
										</div>
										<div id="modal-body" className="mt-2">
											{children}
										</div>
									</div>
								</div>
							</div>
						</Transition.Child>
					</>
				</div>
			</Transition>
		</div>
	);
};
