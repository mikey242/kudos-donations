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
	headerContent: ReactNode;
	setHeaderContent: Dispatch<SetStateAction<ReactNode>>;
	setPageTitle: Dispatch<SetStateAction<string>>;
	searchParams: URLSearchParams;
	updateParam: (name: string, value: string) => void;
	updateParams: (params: { name: string; value: string }[]) => void;
	deleteParams: (params: string[]) => void;
}

interface ProviderProps {
	children: ReactNode;
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

	const updateParam = (name: string, value: string) => {
		searchParams.set(name, value);
		setSearchParams(searchParams);
	};

	const updateParams = (params: { name: string; value: string }[]) => {
		params.forEach((param) => {
			searchParams.set(param.name, param.value);
		});
		setSearchParams(searchParams);
	};

	const deleteParams = useCallback(
		(params: string[]) => {
			params.forEach((param) => {
				searchParams.delete(param);
			});
			setSearchParams(searchParams);
		},
		[searchParams, setSearchParams]
	);

	const data: AdminContextValue = {
		headerContent,
		setHeaderContent,
		updateParam,
		updateParams,
		deleteParams,
		searchParams,
		setPageTitle,
	};

	return (
		<AdminContext.Provider value={data}>
			<AdminHeader />
			<Notices />
			<div className="admin-wrap">
				<h1 style={{ textAlign: 'center' }}>{pageTitle}</h1>
				<>{children}</>
			</div>
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
