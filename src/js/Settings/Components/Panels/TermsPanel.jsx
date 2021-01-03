const {__} = wp.i18n;
const { PanelBody, TextControl} = wp.components;

const TermsPanel = (props ) => {
	return (
		<PanelBody
			title={ __( 'Terms and conditions', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<TextControl
				label={ __( 'URL', 'kudos-donations' ) }
				help={ __( 'The url containing your terms and conditions for the donation. If this is left blank then the \'I agree\' checkbox on the form will not be shown.', 'kudos-donations' ) }
				type={ 'text' }
				value={ props.settings._kudos_terms_link || '' }
				placeholder={ props.placeholder }
				disabled={ props.isSaving }
				onChange={ ( value ) => props.handleInputChange( '_kudos_terms_link', value ) }
			/>

		</PanelBody>
	);
};

export { TermsPanel };
