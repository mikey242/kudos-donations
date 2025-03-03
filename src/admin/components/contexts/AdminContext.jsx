import React, { createContext, useContext, useState } from 'react';
import { BrowserRouter, useSearchParams } from 'react-router-dom';
import { AdminHeader } from '../AdminHeader';
import * as AdminControls from '../../components/controls';

const AdminContext = createContext(null);

export const AdminProvider = ({ children }) => {
	return (
		<BrowserRouter>
			<InnerAdminProvider>{children}</InnerAdminProvider>
		</BrowserRouter>
	);
};

export const InnerAdminProvider = ({ children }) => {
	const [searchParams, setSearchParams] = useSearchParams();
	const [headerContent, setHeaderContent] = useState(null);
	window.kudos.AdminControls = AdminControls;

	const updateParam = (name, value) => {
		searchParams.set(name, value);
		setSearchParams(searchParams);
	};

	const updateParams = (params) => {
		params.forEach((param) => {
			searchParams.set(param.name, param.value);
		});
		setSearchParams(searchParams);
	};

	const deleteParams = (params) => {
		params.forEach((param) => {
			searchParams.delete(param);
		});
		setSearchParams(searchParams);
	};

	const data = {
		headerContent,
		setHeaderContent,
		updateParam,
		updateParams,
		deleteParams,
		searchParams,
	};

	return (
		<AdminContext.Provider value={data}>
			<AdminHeader />
			<>{children}</>
		</AdminContext.Provider>
	);
};

// Custom hook to use the AdminContext
export const useAdminContext = () => useContext(AdminContext);
