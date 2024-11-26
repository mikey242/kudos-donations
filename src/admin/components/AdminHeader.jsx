import React from 'react';
import { KudosLogo } from '../../block/components/KudosLogo';
import { __ } from '@wordpress/i18n';
import {
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
							<Flex
								direction={['column', 'row']}
								justify="flex-start"
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
									<h1>
										{__(
											'Kudos Donations',
											'kudos-donations'
										)}
									</h1>
								</FlexItem>
								<FlexItem>
									<div className="kudos-version">
										{window.kudos?.version}
									</div>
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
