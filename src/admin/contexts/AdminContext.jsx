import React, { createContext, useContext, useState } from 'react';
import { IntroGuide } from '../components/settings/IntroGuide';
import SettingsProvider from './SettingsContext';
import { useSearchParams } from 'react-router-dom';

const AdminContext = createContext(null);

export const AdminProvider = ({ children }) => {
	const [searchParams, setSearchParams] = useSearchParams();
	const [headerContent, setHeaderContent] = useState(null);

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
		<SettingsProvider>
			<AdminContext.Provider value={data}>
				<>
					<IntroGuide />
					{children}
				</>
			</AdminContext.Provider>
		</SettingsProvider>
	);
};

// Custom hook to use the AdminContext
export const useAdminContext = () => useContext(AdminContext);
