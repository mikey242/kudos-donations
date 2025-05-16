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
import { useAdminContext } from './contexts';

export const AdminHeader = (): React.ReactNode => {
	const { headerContent } = useAdminContext();

	return (
		<>
			<Panel className={'kudos-settings-header'}>
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
								{headerContent}
							</Flex>
						</FlexItem>
					</Flex>
				</div>
			</Panel>
		</>
	);
};
