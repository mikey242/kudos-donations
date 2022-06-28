import React from 'react';
import { __ } from '@wordpress/i18n';
import { XIcon } from '@heroicons/react/solid';
import logo from '../../images/logo-colour.svg';
import { Transition } from '@headlessui/react';
import { useEffect } from '@wordpress/element';
import FocusTrap from 'focus-trap-react';

const KudosModal = ({
	toggle,
	isOpen = false,
	children,
	trapFocus = true,
	showLogo = true,
}) => {
	useEffect(() => {
		if (isOpen) {
			document.addEventListener('keydown', handleKeyPress, false);
		}

		return () =>
			document.removeEventListener('keydown', handleKeyPress, false);
	}, [isOpen]);

	const handleKeyPress = (e) => {
		if (e.key === 'Escape' || e.keyCode === 27) toggle();
	};

	return (
		<Transition show={isOpen} appear={true}>
			<FocusTrap active={trapFocus}>
				<div className="fixed z-[999999] inset-0 overflow-y-auto">
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
								className="fixed inset-0 bg-black/30"
								aria-hidden="true"
							/>
						</Transition.Child>
						<Transition.Child
							enter="transition-all duration-[400ms]"
							enterFrom="opacity-0 rotate-[-5deg] translate-x-3 translate-y-3 scale-90"
							enterTo="opacity-100 rotate-0 translate-x-0 translate-y-0 scale-100"
						>
							<div className="flex min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
								{/* This element is to trick the browser into centering the modal contents. */}
								<span
									className="hidden sm:inline-block sm:align-middle sm:h-screen"
									aria-hidden="true"
								>
									&#8203;
								</span>
								<div
									id="kudos-modal"
									className="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all duration-200 sm:align-middle sm:max-w-lg w-full"
								>
									<div className="bg-white p-8">
										<div className="kudos-modal-header flex items-center justify-between">
											{showLogo && (
												<span
													className="mr-3 inline-block flex"
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
												<XIcon className="align-middle w-5 h-5" />
											</button>
										</div>
										<div className="mt-2">{children}</div>
									</div>
								</div>
							</div>
						</Transition.Child>
					</>
				</div>
			</FocusTrap>
		</Transition>
	);
};

export default KudosModal;
