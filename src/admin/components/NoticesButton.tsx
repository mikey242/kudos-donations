import { Button, Dropdown, Notice } from '@wordpress/components';
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
	const count = notices.length;

	if (count === 0) {
		return null;
	}

	return (
		<Dropdown
			style={{ position: 'relative', lineHeight: 0 }}
			focusOnMount={false}
			popoverProps={{ placement: 'bottom-end' }}
			renderToggle={({ isOpen, onToggle }) => (
				<>
					<Button
						icon={bell}
						style={{
							color: 'var(--wp-admin-theme-color)',
							border: '1px solid var(--wp-admin-theme-color)',
							borderRadius: '50%',
						}}
						label={`${count} notification${count !== 1 ? 's' : ''}`}
						onClick={onToggle}
						aria-expanded={isOpen}
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
				</>
			)}
			renderContent={() => (
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
			)}
		/>
	);
};
