import React, {
	createContext,
	Dispatch,
	ReactNode,
	SetStateAction,
	useContext,
	useState,
} from 'react';
import {
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalSpacer as Spacer,
	SlotFillProvider,
} from '@wordpress/components';
import { AdminHeader, MigrationModal, Notices } from '../components';
import { NuqsAdapter } from 'nuqs/adapters/react';

interface AdminContextValue {
	setPageTitle: Dispatch<SetStateAction<string>>;
	pageTitle: string;
}

interface ProviderProps {
	children: ReactNode;
}

const AdminContext = createContext<AdminContextValue | null>(null);

export const AdminProvider = ({ children }: ProviderProps) => {
	const needsUpgrade = window.kudos?.needsUpgrade ?? false;

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
	const [pageTitle, setPageTitle] = useState<string>('');

	// Define export data.
	const data: AdminContextValue = {
		setPageTitle,
		pageTitle,
	};

	return (
		<AdminContext.Provider value={data}>
			<SlotFillProvider>
				<AdminHeader />
				<Notices />
				<Spacer marginTop={'7'} />
				{pageTitle && (
					<h1 style={{ textAlign: 'center' }}>{pageTitle}</h1>
				)}
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
