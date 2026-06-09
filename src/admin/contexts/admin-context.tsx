import React, { ReactNode } from 'react';
import { Flex, SlotFillProvider } from '@wordpress/components';
import { AdminHeader, MigrationModal, Notices } from '../pages';
import { OnboardingBanner } from '../components';
import { NuqsAdapter } from 'nuqs/adapters/react';
import { createContext, useContext, useState } from '@wordpress/element';
import { SettingsProvider } from './settings-context';

interface AdminContextValue {
	setPageTitle: (title: string | null) => void;
}

interface ProviderProps {
	children: ReactNode;
}

const AdminContext = createContext<AdminContextValue | null>(null);

export const AdminProvider = ({ children }: ProviderProps) => {
	const needsUpgrade = window.kudos?.admin?.needsUpgrade ?? false;

	if (needsUpgrade) {
		return <MigrationModal />;
	}

	return (
		<NuqsAdapter>
			<InnerAdminProvider>{children}</InnerAdminProvider>
		</NuqsAdapter>
	);
};

export const InnerAdminProvider = ({ children }) => {
	const [pageTitle, setPageTitle] = useState<string | null>(null);

	return (
		<AdminContext.Provider value={{ setPageTitle }}>
			<SlotFillProvider>
				<AdminHeader />
				<Notices />
				<SettingsProvider>
					<OnboardingBanner />
					{pageTitle && (
						<Flex justify="center">
							<h2>{pageTitle}</h2>
						</Flex>
					)}
					<>{children}</>
				</SettingsProvider>
			</SlotFillProvider>
		</AdminContext.Provider>
	);
};

// Custom hook to use the AdminContext
export const useAdminContext = (): AdminContextValue => {
	const context = useContext(AdminContext);
	if (!context) {
		throw new Error(
			'useAdminContext must be used within a AdminContextProvider'
		);
	}
	return context;
};
