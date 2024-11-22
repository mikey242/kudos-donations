import React from 'react';

function BaseTab(props) {
	const { title, description, children } = props;

	return (
		<div className="kudos-form-tab block w-full relative mt-4 p-0">
			<legend className="block m-auto">
				<h2 className="font-bold font-heading text-3xl sm:text-4xl/4 m-0 mb-2 block text-center">
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