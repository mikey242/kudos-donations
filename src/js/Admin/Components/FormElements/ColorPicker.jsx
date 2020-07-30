import _uniqueId from 'lodash/uniqueId';

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
			id={ _uniqueId( props.label ) }
		>
			<ColorPalette
				key={ 'key_' + props.id }
				colors={ colors }
				value={ props.value }
				onChange={ ( value ) => props.onChange( props.id, value ) }
				disableCustomColors
				clearable={ false }
			/>
		</BaseControl>
	);
};

export { ColorPicker };
