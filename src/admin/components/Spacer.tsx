interface SpacerProps {
	size: number;
}

export const Spacer = ({ size }: SpacerProps) => (
	<div style={{ marginTop: `${size * 4}px` }} />
);
