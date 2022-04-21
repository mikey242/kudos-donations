import React from 'react'
import { FormProvider, useForm } from 'react-hook-form'

import TabPanel from './TabPanel'

function SettingsEdit ({ settings, updateSettings, tabs }) {
  const methods = useForm({
    defaultValues: settings
  })

  const onSubmit = (data) => {
    updateSettings(data)
  }

  return (
        <FormProvider {...methods}>
            <form id="settings-form" onSubmit={methods.handleSubmit(onSubmit)}>
                <TabPanel
                    values={settings}
                    submitData={updateSettings}
                    tabs={tabs}
                />
            </form>
        </FormProvider>
  )
}

export default SettingsEdit
