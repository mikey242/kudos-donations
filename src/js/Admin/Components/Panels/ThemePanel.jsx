const { __ } = wp.i18n;

const { PanelBody } = wp.components;

import { ColorPicker } from '../FormElements/ColorPicker';

const ThemePanel = (props ) => {
	return (
		<PanelBody
			title={ __( 'Theme Colour', 'kudos-donations' ) }
			initialOpen={ false }
		>
			<ColorPicker
				value={ props.settings._kudos_theme_color }
				onChange={ props.handleInputChange }
				disableCustomColors
			/>

		</PanelBody>
	);
};

export { ThemePanel };
