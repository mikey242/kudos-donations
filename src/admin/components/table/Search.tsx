import { TextControl } from '@wordpress/components';
import { useCallback, useEffect, useState } from '@wordpress/element';
import React from 'react';
import { useAdminQueryParams } from '../../hooks';

export const Search = () => {
	const { params, setParams } = useAdminQueryParams();
	const [input, setInput] = useState(params.search || '');

	// Debounce search updates
	useEffect(() => {
		const timeout = setTimeout(() => {
			void setParams({ search: input });
		}, 300);

		return () => clearTimeout(timeout);
	}, [input, setParams]);

	const onChange = useCallback((value: string) => {
		setInput(value);
	}, []);

	const onSubmit = useCallback(
		(e: React.FormEvent) => {
			e.preventDefault();
			void setParams({ search: input });
		},
		[input, setParams]
	);

	return (
		<form onSubmit={onSubmit}>
			<TextControl
				aria-label="Search"
				placeholder="Search posts..."
				type="search"
				value={input}
				onChange={onChange}
			/>
		</form>
	);
};
