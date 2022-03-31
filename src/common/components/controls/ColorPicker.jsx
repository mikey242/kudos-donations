import { Controller } from 'react-hook-form'
import React from 'react'
import { HexColorPicker, HexColorInput } from 'react-colorful'
import { useState } from '@wordpress/element'

const ColorPicker = ({ name, label }) => {
  const [showPicker, setShowPicker] = useState(false)
  const togglePicker = () => {
    setShowPicker(!showPicker)
  }
  return (
        <Controller
            name={name}
            render={({ field: { onChange, value } }) => (
                <div className="inline-flex relative items-center mt-2">
                    <div
                        className="inline-block w-8 h-8 mr-2 rounded cursor-pointer"
                        style={{ backgroundColor: value }} onClick={togglePicker}/>
                    {showPicker &&
                        <div
                            className="absolute bg-white mt-2 p-5 top-full rounded-lg drop-shadow-md z-1050">
                            <HexColorPicker color={value} onChange={onChange}/>
                            <HexColorInput
                                className={'border-gray-300 mt-2 placeholder-gray-500 border border-solid transition ease-in-out duration-75 leading-6 text-gray-700 bg-white focus:border-primary focus:outline-none focus:ring-0 py-2 px-3 rounded w-full'}
                                color={value} onChange={onChange}/>
                        </div>
                    }
                    <span>{label}</span>
                </div>
            )}
        />
  )
}

export default ColorPicker
