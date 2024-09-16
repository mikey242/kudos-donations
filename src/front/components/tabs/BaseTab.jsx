import React from 'react';

function BaseTab(props) {
	const { title, description, children } = props;

	return (
		<div className="form-tab block w-full relative mt-4 p-0">
			<legend className="block m-auto">
				<h2 className="font-normal font-heading text-3xl sm:text-4xl m-0 mb-4 block text-center">
					{title}
				</h2>
			</legend>
			<p className="text-lg text-center block font-normal mb-4">
				{description}
			</p>
			{children}
		</div>
	);
}

export default BaseTab;
