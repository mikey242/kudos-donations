import { TextControl } from '@wordpress/components';
import { useCallback, useEffect, useState } from '@wordpress/element';
import React from 'react';
import { useAdminQueryParams } from '../../hooks';

export const Search = () => {
	const { params, updateParams } = useAdminQueryParams();
	const [input, setInput] = useState(params.search || '');

	// Debounce search updates
	useEffect(() => {
		const timeout = setTimeout(() => {
			void updateParams({ search: input });
		}, 300);

		return () => clearTimeout(timeout);
	}, [input, updateParams]);

	const onChange = useCallback((value: string) => {
		setInput(value);
	}, []);

	const onSubmit = useCallback(
		(e: React.FormEvent) => {
			e.preventDefault();
			void updateParams({ search: input });
		},
		[input, updateParams]
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
