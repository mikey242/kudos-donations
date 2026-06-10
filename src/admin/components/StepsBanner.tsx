import React from 'react';
import { Button, Card, CardBody, Icon } from '@wordpress/components';

type StepState = 'done' | 'locked' | 'active';

const stepStateStyles: Record<
	StepState,
	{ background: string; circleBackground: string; border?: string }
> = {
	done: {
		background: 'rgba(53, 172, 53, 0.1)',
		circleBackground: 'var(--kudos-colour-success)',
	},
	locked: {
		background: 'rgba(0,0,0,0.04)',
		circleBackground: '#bbb',
	},
	active: {
		background: 'rgba(46, 196, 182, 0.1)',
		circleBackground: 'var(--wp-admin-theme-color)',
		border: '1px solid var(--wp-admin-theme-color)',
	},
};

export interface StepsBannerStep {
	id: string;
	label: string;
	done: boolean;
	hidden?: boolean;
	onClick: () => void;
}

interface StepsBannerProps {
	title: string;
	counterLabel?: string;
	steps: StepsBannerStep[];
	className?: string;
	completedMessage?: React.ReactNode;
	onClose?: () => void;
}

export const StepsBanner = ({
	title,
	counterLabel,
	steps,
	className,
	completedMessage,
	onClose,
}: StepsBannerProps) => {
	const visibleSteps = steps.filter((s) => !s.hidden);
	const resolvedSteps = visibleSteps.map((step, i) => {
		const locked = visibleSteps.slice(0, i).some((s) => !s.done);
		let state: StepState = 'active';
		if (step.done) {
			state = 'done';
		} else if (locked) {
			state = 'locked';
		}
		return {
			...step,
			locked,
			number: i + 1,
			styles: stepStateStyles[state],
		};
	});

	const doneCount = visibleSteps.filter((s) => s.done).length;
	const showCompleted =
		!!completedMessage && visibleSteps.every((s) => s.done);

	return (
		<Card
			className={className}
			size="large"
			style={{ position: 'relative' }}
		>
			<div
				style={{
					height: '4px',
					background: '#e0e0e0',
					borderRadius: '2px 2px 0 0',
				}}
			>
				<div
					style={{
						height: '100%',
						width: `${(doneCount / resolvedSteps.length) * 100}%`,
						background: 'var(--kudos-colour-success)',
						borderRadius: '2px 2px 0 0',
						transition: 'width 0.3s ease',
					}}
				/>
			</div>
			{onClose && (
				<Button
					icon="no"
					onClick={onClose}
					style={{
						position: 'absolute',
						right: '1em',
						top: '1em',
						borderRadius: '50%',
					}}
				/>
			)}
			<CardBody>
				{!showCompleted ? (
					<>
						<h2 style={{ textAlign: 'center', marginTop: 0 }}>
							{title}
						</h2>
						<div
							style={{
								display: 'flex',
								alignItems: 'center',
								justifyContent: 'space-between',
							}}
						>
							{counterLabel && (
								<strong style={{ whiteSpace: 'nowrap' }}>
									{counterLabel} ({doneCount}/
									{resolvedSteps.length})
								</strong>
							)}
							<div
								style={{
									display: 'flex',
									alignItems: 'center',
									justifyContent: 'space-evenly',
									flex: 1,
								}}
							>
								{resolvedSteps.map((step) => (
									<Button
										key={step.id}
										style={{
											background: step.styles.background,
											border: step.styles.border,
											borderRadius: '20px',
										}}
										onClick={step.onClick}
										disabled={step.done || step.locked}
										icon={
											step.done ? (
												<Icon
													icon="yes-alt"
													style={{
														color: 'var(--kudos-colour-success)',
														flexShrink: 0,
													}}
												/>
											) : (
												<span
													style={{
														display: 'inline-flex',
														alignItems: 'center',
														justifyContent:
															'center',
														width: '20px',
														height: '20px',
														borderRadius: '50%',
														background:
															step.styles
																.circleBackground,
														color: 'white',
														fontSize: '11px',
														fontWeight: 600,
														flexShrink: 0,
													}}
												>
													{step.number}
												</span>
											)
										}
									>
										{step.label}
									</Button>
								))}
							</div>
						</div>
					</>
				) : (
					completedMessage
				)}
			</CardBody>
		</Card>
	);
};
