const { __ } = wp.i18n;

const {
	PanelBody,
	PanelRow,
	BaseControl,
	TextControl
} = wp.components;

const AVGPanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'AVG', 'kudos-donations' ) }
			initialOpen={ false }
		>
			<PanelRow>
				<BaseControl
					id={ "_kudos_privacy_link" }
					label={ __( 'Privacy Policy URL', 'kudos-donations' ) }
				>
					<TextControl
						id={ "_kudos_privacy_link" }
						type={ 'text' }
						value={ props.settings._kudos_privacy_link || '' }
						placeholder={ props.placeholder }
						disabled={ props.isSaving }
						onChange={ ( value ) => props.handleInputChange( "_kudos_privacy_link", value ) }
					/>
				</BaseControl>
			</PanelRow>

		</PanelBody>
	);
};

export { AVGPanel };
