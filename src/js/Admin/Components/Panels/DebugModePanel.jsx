import { Toggle } from '../FormElements/Toggle';

const { __ } = wp.i18n;
const { PanelBody } = wp.components;

const DebugModePanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'Debug Mode', 'kudos-donations' ) }
			initialOpen={ false }
		>
			<Toggle
				id="_kudos_debug_mode"
				label={ __( 'Enable debug mode', 'kudos-donations' ) }
				help={ __(
					'This will enable the debug logging and a debug menu found under Kudos.',
					'kudos-donations'
				) }
				value={ props.settings._kudos_debug_mode }
				onChange={ props.handleInputChange }
			/>
		</PanelBody>
	);
};

export { DebugModePanel };
