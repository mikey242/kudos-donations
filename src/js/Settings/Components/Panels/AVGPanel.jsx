const {__} = wp.i18n;
const { PanelBody, TextControl} = wp.components;

const AVGPanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'AVG', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<TextControl
				label={ __( 'Privacy Policy URL', 'kudos-donations' ) }
				type={ 'text' }
				value={ props.settings._kudos_privacy_link || '' }
				placeholder={ props.placeholder }
				disabled={ props.isSaving }
				onChange={ ( value ) => props.handleInputChange( "_kudos_privacy_link", value ) }
			/>

		</PanelBody>
	);
};

export { AVGPanel };
