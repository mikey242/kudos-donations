import React, { createContext, useContext, useState } from 'react';
import { IntroGuide } from '../components/settings/IntroGuide';
import SettingsProvider from './SettingsContext';

// Create the context
const AdminContext = createContext(null);

// Create a provider component
export const AdminProvider = ({ children }) => {
	const [headerContent, setHeaderContent] = useState(null);

	return (
		<SettingsProvider>
			<AdminContext.Provider value={{ headerContent, setHeaderContent }}>
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
