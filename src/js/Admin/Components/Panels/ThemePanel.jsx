const { __ } = wp.i18n;

const {
	PanelBody,
	BaseControl,
	ColorPalette
} = wp.components;

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

			<BaseControl
				id="_kudos_theme_color"
				label={ __( 'Colour', 'kudos-donations' ) }
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
