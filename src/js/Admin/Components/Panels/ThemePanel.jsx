const { __ } = wp.i18n;

const { PanelBody } = wp.components;

import { ColorPicker } from '../FormElements/ColorPicker';

const ThemePanel = (props ) => {

	const colors = [
		{ name: 'orange', color: '#ff9f1c' },
		{ name: 'green', color: '#2ec4b6' },
	];

	return (
		<PanelBody
			title={ __( 'Theme Colour', 'kudos-donations' ) }
			initialOpen={ false }
		>
			<ColorPicker
				id="_kudos_theme_color"
				colors={ colors }
				value={ props.settings._kudos_theme_color }
				onChange={ props.handleInputChange }
				disableCustomColors
			/>

		</PanelBody>
	);
};

export { ThemePanel };
