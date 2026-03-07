import React from 'react';
import { KudosLogo } from '../../block/components';
import { __ } from '@wordpress/i18n';
import {
	FlexBlock,
	FlexItem,
	Flex,
	Panel,
	ResponsiveWrapper,
	Slot,
} from '@wordpress/components';
import { AdminMenu } from './AdminMenu';

export const AdminHeader = (): React.ReactNode => {
	return (
		<>
			<Panel className="kudos-admin-header">
				<div className="kudos-admin-header-top admin-wrap-wide">
					<Flex align="center" justify="space-between">
						<FlexBlock>
							<Flex
								direction="row"
								justify="flex-start"
								align="center"
							>
								<FlexItem>
									<ResponsiveWrapper
										naturalHeight={32}
										naturalWidth={32}
									>
										<KudosLogo />
									</ResponsiveWrapper>
								</FlexItem>
								<FlexItem>
									<span
										style={{
											fontSize: '1.5rem',
											lineHeight: '1.5rem',
											fontWeight: 600,
										}}
										className="kudos-title"
									>
										{__(
											'Kudos Donations',
											'kudos-donations'
										)}
									</span>
								</FlexItem>
								<FlexItem>
									<p className="kudos-version">
										{window.kudos?.version}
									</p>
								</FlexItem>
							</Flex>
						</FlexBlock>
						<FlexItem>
							<Slot
								name="KudosHeaderActions"
								bubblesVirtually
								style={{
									display: 'flex',
									alignItems: 'center',
									gap: '8px',
								}}
							/>
						</FlexItem>
					</Flex>
				</div>
				<AdminMenu />
			</Panel>
		</>
	);
};
