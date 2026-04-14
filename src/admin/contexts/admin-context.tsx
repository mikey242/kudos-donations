import React, { ReactNode } from 'react';
import { Slot, SlotFillProvider } from '@wordpress/components';
import { Spacer } from '../components';
import { AdminHeader, MigrationModal, Notices } from '../pages';
import { NuqsAdapter } from 'nuqs/adapters/react';
import { SLOT_PAGE_TITLE } from '../slot-names';
import { createContext, useContext } from '@wordpress/element';

// eslint-disable-next-line @typescript-eslint/no-empty-interface
interface AdminContextValue {}

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
	return (
		<AdminContext.Provider value={{}}>
			<SlotFillProvider>
				<AdminHeader />
				<Notices />
				<Spacer size={7} />
				<Slot name={SLOT_PAGE_TITLE} />
				<>{children}</>
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
