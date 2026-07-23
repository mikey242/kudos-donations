import { useState } from '@wordpress/element';
import { Button, Notice, Popover } from '@wordpress/components';
import { bell } from '@wordpress/icons';
import type { AdminNotice } from '../hooks/use-admin-notices';
import type { ReactNode } from 'react';

interface NoticesButtonProps {
	notices: AdminNotice[];
	onRemove: (id: string) => void;
}

export const NoticesButton = ({
	notices,
	onRemove,
}: NoticesButtonProps): ReactNode => {
	const [isOpen, setIsOpen] = useState(false);
	const [anchor, setAnchor] = useState<Element | null>(null);
	const count = notices.length;

	if (count === 0) {
		return null;
	}

	return (
		<div ref={setAnchor} style={{ position: 'relative', lineHeight: 0 }}>
			<Button
				icon={bell}
				style={{
					color: 'var(--wp-admin-theme-color)',
					border: '1px solid var(--wp-admin-theme-color)',
					borderRadius: '50%',
				}}
				label={`${count} notification${count !== 1 ? 's' : ''}`}
				onClick={() => setIsOpen((o) => !o)}
			/>
			<span
				style={{
					position: 'absolute',
					top: '-4px',
					right: '-4px',
					background: '#cc1818',
					color: '#fff',
					borderRadius: '50%',
					fontSize: '10px',
					width: '18px',
					height: '18px',
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'center',
					fontWeight: 600,
					pointerEvents: 'none',
					zIndex: 1,
				}}
			>
				{count}
			</span>
			{isOpen && (
				<Popover
					anchor={anchor}
					placement="bottom-end"
					onClose={() => setIsOpen(false)}
					focusOnMount={true}
					onFocusOutside={() => setIsOpen(false)}
				>
					<div
						style={{
							minWidth: '320px',
							maxWidth: '480px',
							padding: '8px',
							display: 'grid',
							gap: '4px',
						}}
					>
						{notices.map((notice) => (
							<Notice
								isDismissible={notice.isDismissible}
								key={notice.id}
								status={notice.status}
								onDismiss={() => onRemove(notice.id)}
								__unstableHTML
							>
								{notice.content}
							</Notice>
						))}
					</div>
				</Popover>
			)}
		</div>
	);
};
