const {__} = wp.i18n;
const { PanelBody, TextControl} = wp.components;

const PrivacyPolicyPanel = (props ) => {
	return (
		<PanelBody
			title={ __( 'Privacy Policy', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<TextControl
				label={ __( 'URL', 'kudos-donations' ) }
				type={ 'text' }
				value={ props.settings._kudos_privacy_link || '' }
				placeholder={ props.placeholder }
				disabled={ props.isSaving }
				onChange={ ( value ) => props.handleInputChange( "_kudos_privacy_link", value ) }
			/>

		</PanelBody>
	);
};

export { PrivacyPolicyPanel };
