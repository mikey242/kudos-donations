// @see https://medium.com/cstech/achieving-focus-trapping-in-a-react-modal-component-3f28f596f35b
// @see https://dev.to/open-wc/mind-the-document-activeelement-2o9a
import { __ } from '@wordpress/i18n';
import logo from '../../images/logo-colour.svg';
import { Transition, TransitionChild } from '@headlessui/react';
import { useCallback, useEffect, useRef } from '@wordpress/element';
import { XMarkIcon } from '@heroicons/react/24/outline';
import React, { ReactNode } from 'react';

const FOCUSABLE =
	'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

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

	// Read through a ref so the keydown listener needn't be re-attached when the parent
	// re-renders with a new callback.
	const toggleRef = useRef(toggleModal);
	toggleRef.current = toggleModal;

	const focusInitialField = useCallback(() => {
		targetRef.current
			?.querySelector<HTMLElement>('[name*="value"]:not([type="hidden"])')
			?.focus();
	}, []);

	useEffect(() => {
		if (!isOpen) {
			return;
		}

		// The form swaps its fields as the donor moves between steps, so the focusable
		// elements are looked up on each Tab press rather than once when the modal opens.
		const handleKeyDown = (e: KeyboardEvent) => {
			if (e.key === 'Escape') {
				toggleRef.current();
				return;
			}
			if (e.key !== 'Tab' || !targetRef.current) {
				return;
			}
			const focusable = Array.from(
				targetRef.current.querySelectorAll<HTMLElement>(FOCUSABLE)
			).filter(
				// Skip disabled fields and ones not rendered (hidden inputs, display: none).
				(el) =>
					!el.hasAttribute('disabled') &&
					el.getClientRects().length > 0
			);
			if (focusable.length === 0) {
				return;
			}
			const first = focusable[0];
			const last = focusable[focusable.length - 1];
			const active = (targetRef.current.getRootNode() as Document)
				.activeElement;
			const isInside = targetRef.current.contains(active);

			if (e.shiftKey && (active === first || !isInside)) {
				e.preventDefault();
				last.focus();
			} else if (!e.shiftKey && (active === last || !isInside)) {
				e.preventDefault();
				first.focus();
			}
		};

		focusInitialField();
		const html = document.documentElement;
		// Reserve the scrollbar's space only if there is one, or a page without one would shift.
		html.classList.toggle(
			'kudos-modal-gutter',
			window.innerWidth > html.clientWidth
		);
		html.classList.add('kudos-modal-open');
		document.addEventListener('keydown', handleKeyDown, false);
		return () => {
			html.classList.remove('kudos-modal-open', 'kudos-modal-gutter');
			document.removeEventListener('keydown', handleKeyDown, false);
		};
	}, [isOpen, focusInitialField]);

	return (
		<div id="modal-container" ref={targetRef}>
			<Transition show={isOpen}>
				<div className="fixed z-[999999] inset-0 overflow-y-scroll">
					<TransitionChild
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
					</TransitionChild>
					<TransitionChild
						beforeEnter={focusInitialField}
						enter="transition-all duration-[400ms]"
						enterFrom="opacity-0 rotate-[-5deg] translate-x-3 translate-y-3 scale-90"
						enterTo="opacity-100 rotate-0 translate-x-0 translate-y-0 scale-100"
						leave="transition-all duration-200"
						leaveFrom="opacity-100 rotate-0 translate-y-0 scale-100"
						leaveTo="opacity-0 translate-y-3 scale-90"
					>
						<div className="flex p-0 xs:px-4 xs:pb-20 text-center xs:block sm:p-0">
							{/* This element is to trick the browser into centering the modal contents. */}
							<span
								className="hidden sm:inline-block sm:align-middle sm:h-screen"
								aria-hidden="true"
							>
								&#8203;
							</span>
							<div
								id="modal"
								className="inline-block bg-white xs:rounded-lg text-left overflow-hidden shadow-xl transform transition-all duration-200 sm:align-middle sm:max-w-lg w-full"
							>
								<div className="bg-white p-4 xs:p-8">
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
											onClick={toggleModal}
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
					</TransitionChild>
				</div>
			</Transition>
		</div>
	);
};
