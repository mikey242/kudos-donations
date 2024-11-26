import React from 'react';
import { KudosLogo } from '../../block/components/KudosLogo';
import { __ } from '@wordpress/i18n';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalDivider as Divider,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalHeading as Heading,
	Dashicon,
	Flex,
	FlexBlock,
	FlexItem,
	Panel,
	ResponsiveWrapper,
} from '@wordpress/components';
import { useAdminContext } from '../contexts/AdminContext';
import { Notices } from './Notices';

export const AdminHeader = () => {
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
									<Heading level="1" size="22">
										{__(
											'Kudos Donations',
											'kudos-donations'
										)}
									</Heading>
								</FlexItem>
								<FlexItem>
									<p className="kudos-version">
										{window.kudos?.version}
									</p>
								</FlexItem>
								<FlexItem>
									<Divider orientation="vertical" />
								</FlexItem>
								{!window.kudos?.is_premium ? (
									<FlexItem>
										<a href={window.kudos.upgrade_url}>
											Upgrade
										</a>
									</FlexItem>
								) : (
									<FlexItem>
										<div className="kudos-premium-badge">
											<Dashicon
												title={__(
													'Premium active',
													'kudos-donations'
												)}
												icon="star-filled"
											/>
										</div>
									</FlexItem>
								)}
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
			<Notices />
		</>
	);
};
