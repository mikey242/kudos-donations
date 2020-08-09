const { __ } = wp.i18n;

const { ColorPalette, BaseControl } = wp.components;

const ColorPicker = ( props ) => {

	const colors = [
		{ name: 'orange', color: '#ff9f1c' },
		{ name: 'green', color: '#2ec4b6' },
	];

	return (
		<BaseControl
			label={ __( 'Colour', 'kudos-donations' ) }
		>
			<ColorPalette
				colors={ colors }
				value={ props.value }
				onChange={ ( value ) => props.onChange( props.id, value ) }
				disableCustomColors={ props.disableCustomColors }
				clearable={ false }
			/>
		</BaseControl>
	);
};

export { ColorPicker };
