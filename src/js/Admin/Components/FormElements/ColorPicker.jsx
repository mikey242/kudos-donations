const { __ } = wp.i18n;

const { ColorPalette, BaseControl } = wp.components;

const ColorPicker = ( props ) => {

	return (
		<BaseControl
			label={ __( 'Colour', 'kudos-donations' ) }
		>
			<ColorPalette
				colors={ props.colors }
				value={ props.value }
				onChange={ ( value ) => props.onChange( props.id, value ) }
				disableCustomColors={ props.disableCustomColors }
				clearable={ false }
			/>
		</BaseControl>
	);
};

export { ColorPicker };
