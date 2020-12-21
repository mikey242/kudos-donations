const { __ } = wp.i18n;
const { PanelBody, ToggleControl, CheckboxControl, PanelRow } = wp.components;

const AddressFieldPanel = (props ) => {
	return (
		<PanelBody
			title={ __( 'Address Field', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<PanelRow>

				<ToggleControl
					label={__("Enable", 'kudos-donations')}
					checked={ props.settings._kudos_address_enabled || '' }
					onChange={ ( value ) => props.handleInputChange( '_kudos_address_enabled', value ) }
				/>

			</PanelRow>

			{ props.settings._kudos_address_enabled ?

				<PanelRow>

					<CheckboxControl
						label={__("Required", "kudos-donations")}
						checked={ props.settings._kudos_address_required || '' }
						onChange={ ( value ) => props.handleInputChange( '_kudos_address_required', value ) }
					/>

				</PanelRow>

			: '' }

		</PanelBody>
	);
};

export { AddressFieldPanel };
