import React from 'react';
import { __ } from '@wordpress/i18n';

export default function EmptyCampaigns() {
	return (
		<div className="text-center mt-5">
			<svg
				className="mx-auto h-12 w-12 text-gray-400"
				fill="none"
				viewBox="0 0 24 24"
				stroke="currentColor"
				aria-hidden="true"
			>
				<path
					vectorEffect="non-scaling-stroke"
					strokeLinecap="round"
					strokeLinejoin="round"
					strokeWidth={2}
					d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
				/>
			</svg>
			<h3 className="mt-2 text-md font-medium text-gray-900">
				{__('No campaign found.', 'kudos-donations')}
			</h3>
			<p className="mt-1 text-base text-gray-500">
				{__(
					'Click the button below to get started.',
					'kudos-donations'
				)}
			</p>
			<p className="mt-1 text-sm">
				{__(
					'Once created, you can add it anywhere on your site using a shortcode or a block.',
					'kudos-donations'
				)}
			</p>
		</div>
	);
}
