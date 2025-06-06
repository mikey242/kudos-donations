import React, {
	createContext,
	Dispatch,
	ReactNode,
	SetStateAction,
	useContext,
	useState,
} from 'react';
import { BrowserRouter } from 'react-router-dom';
import * as AdminControls from '../components/controls';
import { AdminHeader, Notices } from '../components';
// eslint-disable-next-line import/no-unresolved
import { NuqsAdapter } from 'nuqs/adapters/react-router/v7';

interface AdminContextValue {
	setHeaderContent: Dispatch<SetStateAction<ReactNode>>;
	setPageTitle: Dispatch<SetStateAction<string>>;
	pageTitle: string;
}

interface ProviderProps {
	children: ReactNode;
}

const AdminContext = createContext<AdminContextValue | null>(null);

export const AdminProvider = ({ children }: ProviderProps) => {
	return (
		<NuqsAdapter>
			<BrowserRouter>
				<InnerAdminProvider>{children}</InnerAdminProvider>
			</BrowserRouter>
		</NuqsAdapter>
	);
};

export const InnerAdminProvider = ({ children }) => {
	const [headerContent, setHeaderContent] = useState<ReactNode>(null);
	const [pageTitle, setPageTitle] = useState<string>('');

	// Add controls to kudos property for external access.
	window.kudos.AdminControls = AdminControls;

	// Define export data.
	const data: AdminContextValue = {
		setHeaderContent,
		setPageTitle,
		pageTitle,
	};

	return (
		<AdminContext.Provider value={data}>
			<AdminHeader children={headerContent} />
			<Notices />
			<h1 style={{ textAlign: 'center' }}>{pageTitle}</h1>
			<>{children}</>
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
