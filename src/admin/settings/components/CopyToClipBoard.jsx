import { useEffect, useRef } from '@wordpress/element';
import { Button } from '@wordpress/components';

import { useCopyToClipboard } from '@wordpress/compose';
import { ButtonIcon } from './ButtonIcon';

const CopyToClipBoard = ({ text, children, onCopy, onFinishCopy }) => {
	const timeoutId = useRef();
	const ref = useCopyToClipboard(text, () => {
		onCopy();
		clearTimeout(timeoutId.current);

		if (onFinishCopy) {
			timeoutId.current = setTimeout(() => onFinishCopy(), 4000);
		}
	});

	useEffect(() => {
		clearTimeout(timeoutId.current);
	}, []);

	const focusOnCopyEventTarget = (event) => {
		event.target.focus();
	};

	return (
		<div>
			<Button
				isSecondary
				ref={ref}
				icon={<ButtonIcon icon="copy" />}
				onCopy={focusOnCopyEventTarget}
			>
				{children}
			</Button>
		</div>
	);
};

export { CopyToClipBoard };
