import { __ } from '@wordpress/i18n';
import logo from '../../assets/images/logo-colour.svg';
import { Transition } from '@headlessui/react';
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { XMarkIcon } from '@heroicons/react/24/outline';

const KudosModal = ({
	isOpen = false,
	toggleModal,
	children,
	showLogo = true,
}) => {
	const [didLoad, setDidLoad] = useState(false);
	const targetRef = useRef(null);
	let focusableElements;
	let firstElement;
	let lastElement;

	const toggle = useCallback(() => {
		if (typeof toggleModal === 'function') toggleModal();
	}, [toggleModal]);

	const setUp = () => {
		focusableElements = targetRef.current?.querySelectorAll(
			'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
		);
		firstElement = focusableElements && focusableElements[0];
		lastElement =
			focusableElements &&
			focusableElements[focusableElements?.length - 1];
		const initialFocus = targetRef.current?.querySelector(
			'[name*="value"]:not([type="hidden"])'
		);
		initialFocus?.focus();
		addEventListeners();
	};

	const addEventListeners = () => {
		document.addEventListener('keydown', handleKeyPress, false);
	};

	const handleKeyPress = useCallback(
		(e) => {
			if (e.key === 'Escape' || e.keyCode === 27) toggle();
			if (e.key === 'Tab' || e.keyCode === 9) {
				const activeElement =
					targetRef.current.getRootNode().activeElement;
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
		document.body.style.overflowY = isOpen ? 'hidden' : 'auto';
		return () =>
			document.removeEventListener('keydown', handleKeyPress, false);
	}, [handleKeyPress, isOpen]);

	useEffect(() => {
		if (!didLoad) {
			// Watch for data-toggle attribute changes.
			const observer = new MutationObserver((mutations) => {
				mutations.forEach((mutation) => {
					if (
						mutation.type === 'attributes' &&
						mutation.attributeName === 'data-toggle' &&
						targetRef.current.dataset.toggle === 'true'
					) {
						toggleModal();
						targetRef.current.dataset.toggle = 'false';
					}
				});
			});

			setDidLoad(true);
			if (targetRef.current) {
				observer.observe(targetRef.current, { attributes: true });
			}
		}
	}, [didLoad, toggleModal]);

	return (
		<div ref={targetRef} data-toggle={false}>
			<Transition show={isOpen} appear={false}>
				<div className={'fixed z-[999999] inset-0 overflow-y-scroll'}>
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
							beforeEnter={setUp}
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
										<div className="mt-2">{children}</div>
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

export default KudosModal;
