const { __ } = wp.i18n;
const { BaseControl, ColorPalette, PanelBody } = wp.components;

const ThemePanel = (props ) => {

	const colors = [
		{ name: 'orange', color: '#ff9f1c' },
		{ name: 'green', color: '#2ec4b6' },
	];

	return (
		<PanelBody
			title={ __( 'Theme Colour', 'kudos-donations' ) }
			initialOpen={ true }
		>

			<BaseControl
				id="_kudos_theme_color"
				label={ __( 'Colour', 'kudos-donations' ) }
				help={__('Set the colour for the Kudos button and the pop-up modal')}
			>
				<ColorPalette
					id="_kudos_theme_color"
					colors={ colors }
					value={ props.settings._kudos_theme_color }
					onChange={ ( value ) => props.handleInputChange( '_kudos_theme_color', value ) }
					disableCustomColors={ true }
					clearable={ false }
				/>
			</BaseControl>

		</PanelBody>
	);
};

export { ThemePanel };
