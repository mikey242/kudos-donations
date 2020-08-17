const { __ } = wp.i18n;
const { CheckboxControl, PanelBody, PanelRow } = wp.components;

const DonationModalPanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'Donation Form', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<PanelRow>

				<CheckboxControl
					label="Enable address field"
					checked={ props.settings._kudos_address_enabled || '' }
					onChange={ ( value ) => props.handleInputChange( '_kudos_address_enabled', value ) }
				/>

			</PanelRow>

			{ props.settings._kudos_address_enabled
				? [

					<PanelRow>

						<CheckboxControl
							label="Address required"
							checked={ props.settings._kudos_address_required || '' }
							onChange={ ( value ) => props.handleInputChange( '_kudos_address_required', value ) }
						/>

					</PanelRow>
				  ]
				: '' }
		</PanelBody>
	);
};

export { DonationModalPanel };
