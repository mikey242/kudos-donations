import { Tab } from '@headlessui/react';
import React from 'react';
import { clsx } from 'clsx';
import { Fragment } from '@wordpress/element';
import { NumberParam, useQueryParam } from 'use-query-params';
import Panel from '../Panel';

const TabPanel = ({ tabs }) => {
	const [tabIndex, setTabIndex] = useQueryParam('tab', NumberParam);

	return (
		<div className="mx-auto mt-5 w-full max-w-4xl">
			<Tab.Group
				selectedIndex={tabIndex}
				onChange={(index) => setTabIndex(index)}
			>
				<Panel>
					<Tab.List className="relative z-0 rounded-lg flex divide-x divide-gray-200">
						{Object.entries(tabs).map((tab, index) => (
							<Tab key={index} as={Fragment}>
								{({ selected }) => (
									<button
										key={tab[1].name}
										className={clsx(
											selected
												? 'text-gray-900'
												: 'text-gray-500 hover:text-gray-700',
											index === 0 && 'rounded-l-lg',
											index === tabs.length - 1 &&
												'rounded-r-lg',
											'p-4 group relative min-w-0 flex-1 overflow-hidden text-sm font-medium text-center hover:bg-gray-50 focus:outline-none focus:z-10'
										)}
									>
										{tab[1].title}
										<span
											className={clsx(
												selected
													? 'bg-primary'
													: 'bg-transparent',
												'absolute inset-x-0 bottom-0 h-0.5'
											)}
										/>
									</button>
								)}
							</Tab>
						))}
					</Tab.List>
				</Panel>
				<Tab.Panels>
					<>
						{Object.entries(tabs).map((tab) => {
							tab = tab[1];
							return (
								<Tab.Panel key={tab.name}>
									{tab.content}
								</Tab.Panel>
							);
						})}
					</>
				</Tab.Panels>
			</Tab.Group>
		</div>
	);
};

export default TabPanel;
