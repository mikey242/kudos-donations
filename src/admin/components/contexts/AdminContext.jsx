import React, { createContext, useContext, useState } from 'react';
import { BrowserRouter, useSearchParams } from 'react-router-dom';
import { AdminHeader } from '../AdminHeader';
import * as AdminControls from '../../components/controls';
import { Notices } from '../Notices';

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
	const [pageTitle, setPageTitle] = useState('');

	// Add controls to kudos property for external access.
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
export const useAdminContext = () => useContext(AdminContext);
