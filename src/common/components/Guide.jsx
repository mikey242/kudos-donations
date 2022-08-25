import classnames from 'classnames';
import { useState } from '@wordpress/element';
import {
	useConstrainedTabbing,
	useFocusOnMount,
	useMergeRefs,
} from '@wordpress/compose';
import { ESCAPE, LEFT, RIGHT } from '@wordpress/keycodes';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { Button } from './controls';

const Guide = ({ pages = [], className, onFinish }) => {
	const [currentPage, setCurrentPage] = useState(0);
	const [furthestPage, setFurthestPage] = useState(0);
	const canGoBack = currentPage > 0;
	const canGoForward = currentPage < pages.length - 1;
	const focusOnMountRef = useFocusOnMount(true);
	const constrainedTabbingRef = useConstrainedTabbing();

	const handleKeyPress = (event) => {
		if (event.keyCode === LEFT) {
			goBack();
		}
		if (event.keyCode === RIGHT) {
			goForward();
		}
		if (event.keyCode === ESCAPE) {
			onFinish();
		}
	};

	const goBack = () => {
		if (canGoBack) {
			setCurrentPage(currentPage - 1);
		}
	};

	const goForward = () => {
		if (canGoForward) {
			setFurthestPage(Math.max(currentPage + 1, furthestPage));
			setCurrentPage(currentPage + 1);
		}
	};

	const pageNav = pages.map((page, i) => {
		const isAccessible = furthestPage >= i;
		const currentClass =
			currentPage === i ? 'bg-orange-500' : 'bg-transparent';
		const accessibleClass = isAccessible
			? 'cursor-pointer border-orange-500'
			: 'border-orange-200';
		const classes = classnames(currentClass, accessibleClass);
		return (
			<button
				className={classnames(
					classes,
					'border-2 border-solid m-0 mx-2 rounded-full w-4 h-4'
				)}
				key={i}
				onClick={() => (isAccessible ? setCurrentPage(i) : null)}
			/>
		);
	});

	return (
		<div
			ref={useMergeRefs([focusOnMountRef, constrainedTabbingRef])}
			onKeyDown={(e) => handleKeyPress(e)}
			className={classnames('intro text-base leading-6', className)}
		>
			<div className={'m-auto flex flex-col justify-center items-center'}>
				<div className="intro-content m-auto ">
					<div className="intro-image mb-2">
						<img
							alt="Page graphic"
							className={'w-full'}
							src={pages[currentPage].imageSrc}
						/>
					</div>
					<h1 className={'font-serif text-center mb-2'}>
						{pages[currentPage].heading}
					</h1>
					<div className="text-lg text-center">
						{pages[currentPage].content}
					</div>
				</div>
				<div className="intro-nav w-full pt-5 border-0 border-t border-solid border-gray-200 flex justify-between items-center w-11/12 mt-5 mb-5">
					<Button
						isOutline
						className={canGoBack ? 'visible' : 'invisible'}
						onClick={goBack}
					>
						{__('Previous', 'kudos-donations')}
					</Button>

					<div className={'flex justify-center m-0'}>{pageNav}</div>
					{canGoForward && (
						<Button
							isDisabled={
								pages[currentPage].nextDisabled ?? false
							}
							onClick={goForward}
						>
							{pages[currentPage].hasOwnProperty('nextLabel')
								? pages[currentPage].nextLabel
								: __('Next', 'kudos-donations')}
						</Button>
					)}
					{!canGoForward && (
						<Button onClick={onFinish}>
							{__('Finish', 'kudos-donations')}
						</Button>
					)}
				</div>
			</div>
		</div>
	);
};

export { Guide };
