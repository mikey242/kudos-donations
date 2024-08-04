import React from 'react';
import {
	Fragment,
	useCallback,
	useEffect,
	useRef,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Pane } from '../../common/Panel';
import { ColorPicker } from '../../common/controls';
import { Transition } from '@headlessui/react';

const ColorPickerPopup = ({ color, onColorChange }) => {
	const [shown, setShown] = useState(false);
	const popoverRef = useRef();

	const handleKeyPress = useCallback((event) => {
		if (event.key === 'Escape' || event.keyCode === 27) {
			setShown(false);
		}
	}, []);

	const handleClick = useCallback(
		(event) => {
			if (shown && !event.composedPath().includes(popoverRef.current)) {
				setShown(false);
			}
		},
		[shown]
	);

	const addEventListeners = useCallback(() => {
		window.addEventListener('keydown', handleKeyPress, false);
		window.addEventListener('click', handleClick, false);
	}, [handleClick, handleKeyPress]);

	const removeEventListeners = useCallback(() => {
		window.removeEventListener('keydown', handleKeyPress, false);
		window.removeEventListener('click', handleClick, false);
	}, [handleClick, handleKeyPress]);

	useEffect(() => {
		if (!shown) {
			return;
		}

		/**
		 * Race condition causes popup to immediately close since is true.
		 * Adding the timer prevents this.
		 */
		const timer = setTimeout(() => {
			addEventListeners();
		}, 100);
		return () => {
			clearTimeout(timer);
			removeEventListeners();
		};
	}, [addEventListeners, removeEventListeners, shown]);

	return (
		<>
			<button
				type="button"
				className="w-5 h-5 block rounded-full"
				onClick={() => setShown(!shown)}
				style={{
					backgroundColor: color,
				}}
			/>

			<div className="top-16 w-full max-w-sm px-4">
				{/*<div className="color-picker-overlay fixed inset-0 bg-black/30 z-1"></div>*/}
				<Transition
					show={shown}
					as={Fragment}
					enter="transition ease-out duration-200"
					enterFrom="opacity-0 translate-y-1"
					enterTo="opacity-100 translate-y-0"
					leave="transition ease-in duration-150"
					leaveFrom="opacity-100 translate-y-0"
					leaveTo="opacity-0 translate-y-1"
				>
					<Pane className="absolute p-3" ref={popoverRef}>
						<ColorPicker
							name="meta.theme_color"
							onColorChange={onColorChange}
							altLabel={__('Theme color', 'kudos-donations')}
							help={__(
								'Choose a color theme for your campaign.',
								'kudos-donations'
							)}
						/>
					</Pane>
				</Transition>
			</div>
		</>
	);
};

export { ColorPickerPopup };
