import React, { createContext, useContext, useState } from 'react';

// Create the context
const AdminContext = createContext(null);

// Create a provider component
export const AdminProvider = ({ children }) => {
	const [headerContent, setHeaderContent] = useState(null);

	return (
		<AdminContext.Provider value={{ headerContent, setHeaderContent }}>
			{children}
		</AdminContext.Provider>
	);
};

// Custom hook to use the AdminContext
export const useAdminContext = () => useContext(AdminContext);
