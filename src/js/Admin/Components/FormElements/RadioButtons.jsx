const { PanelRow, RadioControl } = wp.components;

const RadioButtons = ( props ) => {
	return (
		<PanelRow>
			<RadioControl
				key={ 'key_' + props.id }
				label={ props.label }
				help={ props.help }
				selected={ props.selected }
				options={ props.children }
				onChange={ ( value ) => props.onChange( props.id, value ) }
			/>
		</PanelRow>
	);
};

export { RadioButtons };
