const { PanelRow, CheckboxControl } = wp.components;

const Checkbox = ( props ) => {
	return (
		<PanelRow>
			<CheckboxControl
				key={ 'key_' + props.id }
				heading={ props.heading }
				label={ props.label }
				help={ props.help }
				checked={ props.value || '' }
				onChange={ ( value ) => props.onChange( props.id, value ) }
			/>
		</PanelRow>
	);
};

export { Checkbox };
