const { __ } = wp.i18n;
const {
	PanelBody,
	PanelRow,
	CheckboxControl
} = wp.components;
const { Fragment } = wp.element;

const DonationModalPanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'Donation Form', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<PanelRow>
				<CheckboxControl
					heading={ __( 'Address', 'kudos-donations' ) }
					label="Enable address field"
					checked={ props.settings._kudos_address_enabled || '' }
					onChange={ ( value ) => props.handleInputChange( '_kudos_address_enabled', value ) }
				/>
			</PanelRow>

			{ props.settings._kudos_address_enabled
				? [
						<Fragment>
							<PanelRow>
								<CheckboxControl
									label="Address required"
									checked={ props.settings._kudos_address_required || '' }
									onChange={ ( value ) => props.handleInputChange( '_kudos_address_required', value ) }
								/>
							</PanelRow>
						</Fragment>,
				  ]
				: '' }
		</PanelBody>
	);
};

export { DonationModalPanel };
