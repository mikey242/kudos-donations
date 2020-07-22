const { PanelRow, ToggleControl } = wp.components;

const Toggle = ( props ) => {
	return (
		<PanelRow>
			<ToggleControl
				label={ props.label }
				help={ props.help }
				key={ 'key_' + props.id }
				checked={ props.value || '' }
				onChange={ () => props.onChange( props.id, ! props.value ) }
			/>
		</PanelRow>
	);
};

export { Toggle };
