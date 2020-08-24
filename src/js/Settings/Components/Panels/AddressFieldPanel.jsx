const { __ } = wp.i18n;
const { CheckboxControl, PanelBody, PanelRow } = wp.components;

const AddressFieldPanel = (props ) => {
	return (
		<PanelBody
			title={ __( 'Address Field', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<PanelRow>

				<CheckboxControl
					label="Enable"
					checked={ props.settings._kudos_address_enabled || '' }
					onChange={ ( value ) => props.handleInputChange( '_kudos_address_enabled', value ) }
				/>

			</PanelRow>

			{ props.settings._kudos_address_enabled
				? [

					<PanelRow>

						<CheckboxControl
							label="Required"
							checked={ props.settings._kudos_address_required || '' }
							onChange={ ( value ) => props.handleInputChange( '_kudos_address_required', value ) }
						/>

					</PanelRow>
				  ]
				: '' }
		</PanelBody>
	);
};

export { AddressFieldPanel };
