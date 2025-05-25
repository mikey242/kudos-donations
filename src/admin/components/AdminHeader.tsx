import React from 'react';
import { KudosLogo } from '../../block/components/KudosLogo';
import { __ } from '@wordpress/i18n';
import {
	Flex,
	FlexBlock,
	FlexItem,
	Panel,
	ResponsiveWrapper,
} from '@wordpress/components';

interface AdminHeaderProps {
	children: React.ReactNode;
}

export const AdminHeader = ({
	children,
}: AdminHeaderProps): React.ReactNode => {
	return (
		<>
			<Panel className="kudos-admin-header">
				<div className="admin-wrap">
					<Flex align="center" justify="space-between">
						<FlexBlock>
							<Flex direction="row" justify="flex-start">
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
							<Flex justify="flex-end" align="center">
								{children}
							</Flex>
						</FlexItem>
					</Flex>
				</div>
			</Panel>
		</>
	);
};
