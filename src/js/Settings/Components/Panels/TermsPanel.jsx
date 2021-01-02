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
