import { Tab } from '@headlessui/react';
import React from 'react';
import Panel from './Panel';
import classNames from 'classnames';
import { Fragment, useEffect, useState } from '@wordpress/element';
import { getQueryVar, updateQueryParameter } from '../../common/helpers/util';

const TabPanel = ({ tabs }) => {
	const [selectedIndex, setSelectedIndex] = useState(0);
	const query = getQueryVar('tab');

	useEffect(() => {
		setSelectedIndex(query);
	}, []);

	useEffect(() => {
		if (selectedIndex != null) {
			updateQueryParameter('tab', selectedIndex);
		}
	}, [selectedIndex]);

	return (
		<div className="mx-auto mt-5 w-full max-w-[768px]">
			<Tab.Group
				selectedIndex={selectedIndex}
				onChange={setSelectedIndex}
			>
				<Panel>
					<Tab.List className="relative z-0 rounded-lg flex divide-x divide-gray-200">
						{Object.entries(tabs).map((tab, index) => {
							tab = tab[1];
							return (
								<Tab key={index} as={Fragment}>
									{({ selected }) => (
										<button
											key={tab.name}
											className={classNames(
												selected
													? 'text-gray-900'
													: 'text-gray-500 hover:text-gray-700',
												index === 0 && 'rounded-l-lg',
												index === tabs.length - 1 &&
													'rounded-r-lg',
												'group relative min-w-0 flex-1 overflow-hidden bg-white py-4 px-4 text-sm font-medium text-center hover:bg-gray-50 focus:z-10'
											)}
										>
											{tab.title}
											<span
												// aria-hidden="true"
												className={classNames(
													selected
														? 'bg-primary'
														: 'bg-transparent',
													'absolute inset-x-0 bottom-0 h-0.5'
												)}
											/>
										</button>
									)}
								</Tab>
							);
						})}
					</Tab.List>
				</Panel>
				<Tab.Panels>
					<Panel>
						<div className="p-6">
							{Object.entries(tabs).map((tab) => {
								tab = tab[1];
								return (
									<Tab.Panel key={tab.name}>
										{tab.content}
									</Tab.Panel>
								);
							})}
						</div>
					</Panel>
				</Tab.Panels>
			</Tab.Group>
		</div>
	);
};

export default TabPanel;
