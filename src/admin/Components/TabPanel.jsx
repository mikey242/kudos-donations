import { Tab } from '@headlessui/react'
import React from 'react'
import Panel from './Panel'

const TabPanel = ({ tabs }) => {
  return (
        <div className="mx-auto mt-5 w-[768px]">
            <Tab.Group>
                <Panel>
                    <div className="px-2">
                        <Tab.List className="flex justify-around">
                            {Object.entries(tabs).map((tab) => {
                              tab = tab[1]
                              return (
                                    <Tab
                                        key={tab.name}
                                        className={({ selected }) => `
                                            ${selected ? 'text-black font-bold after:content-[""] after:border-b-4' : 'text-gray-500'}
                                            after:border-primary after:absolute after:left-0 after:bottom-0 after:w-full
                                            border-0 py-4 px-2 cursor-pointer rounded-lg text-md relative transition ease-in-out 
                                            
                                        `}
                                    >
                                        {tab.title}
                                    </Tab>
                              )
                            })}
                        </Tab.List>
                    </div>
                </Panel>
                <Tab.Panels>
                    <Panel>
                        <div className="p-6">

                            {Object.entries(tabs).map((tab) => {
                              tab = tab[1]
                              return (
                                    <Tab.Panel key={tab.name}>{tab.content}</Tab.Panel>
                              )
                            })}

                        </div>
                    </Panel>
                </Tab.Panels>
            </Tab.Group>
        </div>
  )
}

export default TabPanel
