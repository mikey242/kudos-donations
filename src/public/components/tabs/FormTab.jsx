import React from 'react';

function FormTab(props) {
	const { title, description, children } = props;

	return (
		<div className="form-tab block w-full relative mt-4 p-0">
			<legend className="block m-auto">
				<h2 className="font-normal font-serif text-4xl m-0 mb-4 text-gray-900 block text-center">
					{title}
				</h2>
			</legend>
			<p className="text-lg text-gray-900 text-center block font-normal mb-4">
				{description}
			</p>
			{children}
		</div>
	);
}

export default FormTab;
