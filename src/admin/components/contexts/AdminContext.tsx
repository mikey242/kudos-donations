import React, {
	createContext,
	Dispatch,
	ReactNode,
	SetStateAction,
	useContext,
	useState,
} from 'react';
import { BrowserRouter, useSearchParams } from 'react-router-dom';
import { AdminHeader } from '../AdminHeader';
import * as AdminControls from '../../components/controls';
import { Notices } from '../Notices';
import { useCallback } from '@wordpress/element';

interface AdminContextValue {
	setHeaderContent: Dispatch<SetStateAction<ReactNode>>;
	setPageTitle: Dispatch<SetStateAction<string>>;
	searchParams: URLSearchParams;
	setQueryParams: (params: SetQueryParamsProps) => void;
}

interface ProviderProps {
	children: ReactNode;
}

interface SetQueryParamsProps {
	set?: { name: string; value: string }[];
	delete?: string[];
	reset?: boolean;
	preserveKeys?: string[];
}

const AdminContext = createContext<AdminContextValue | null>(null);

export const AdminProvider = ({ children }: ProviderProps) => {
	return (
		<BrowserRouter>
			<InnerAdminProvider>{children}</InnerAdminProvider>
		</BrowserRouter>
	);
};

export const InnerAdminProvider = ({ children }) => {
	const [searchParams, setSearchParams] = useSearchParams();
	const [headerContent, setHeaderContent] = useState<ReactNode>(null);
	const [pageTitle, setPageTitle] = useState<string>('');

	// Add controls to kudos property for external access.
	window.kudos.AdminControls = AdminControls;

	const setQueryParams = useCallback(
		(updates: SetQueryParamsProps) => {
			const params = updates.reset
				? new URLSearchParams()
				: new URLSearchParams(searchParams.toString());

			if (updates.reset && updates.preserveKeys?.length) {
				updates.preserveKeys.forEach((key) => {
					const val = searchParams.get(key);
					if (val !== null) {
						params.set(key, val);
					}
				});
			}

			updates.delete?.forEach((key) => {
				params.delete(key);
			});

			updates.set?.forEach(({ name, value }) => {
				params.set(name, value);
			});

			setSearchParams(params);
		},
		[searchParams, setSearchParams]
	);

	const data: AdminContextValue = {
		setHeaderContent,
		searchParams,
		setPageTitle,
		setQueryParams,
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
