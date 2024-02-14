import { Controller } from 'react-hook-form';
import React from 'react';
import CodeMirror from '@uiw/react-codemirror';
import { css } from '@codemirror/lang-css';

const CodeEditor = ({ name, label, help, onChange = () => {} }) => {

    return (
        <Controller
            name={name}
            className="border-gray-300 focus:ring-primary focus:border-primary"
            render={({field: {onChange, value}}) => (
                <>
                    {(label || help) && (
                        <div>
                            {label && (
                                <p className="block text-sm font-bold text-gray-700">
                                    {label}
                                </p>
                            )}

                            {help && (
                                <p className="text-sm text-gray-500 mt-1">
                                    {help}
                                </p>
                            )}
                        </div>
                    )}

                    <CodeMirror
                        value={value}
                        height="400px"
                        extensions={[css()]}
                        onChange={onChange}
                    />
                </>
            )}
        />
    );
};

export {CodeEditor};
