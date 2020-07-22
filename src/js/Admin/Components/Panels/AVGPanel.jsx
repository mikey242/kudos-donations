import { TextInput } from '../FormElements/TextInput';

const { __ } = wp.i18n;

const { PanelBody } = wp.components;

const AVGPanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'AVG', 'kudos-donations' ) }
			initialOpen={ false }
		>
			<TextInput
				id="_kudos_privacy_link"
				label={ __( 'Privacy Policy URL', 'kudos-donations' ) }
				value={ props.settings._kudos_privacy_link }
				disabled={ props.isSaving }
				onChange={ props.handleInputChange }
			/>
		</PanelBody>
	);
};

export { AVGPanel };
