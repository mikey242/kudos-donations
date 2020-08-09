import { Checkbox } from '../FormElements/Checkbox';

const { __ } = wp.i18n;
const { PanelBody } = wp.components;
const { Fragment } = wp.element;

const DonationModalPanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'Donation Form', 'kudos-donations' ) }
			initialOpen={ false }
		>
			<Checkbox
				id="_kudos_address_enabled"
				heading={ __( 'Address', 'kudos-donations' ) }
				label="Enable address field"
				value={ props.settings._kudos_address_enabled }
				onChange={ props.handleInputChange }
			/>

			{ props.settings._kudos_address_enabled
				? [
						<Fragment key="_kudos_address_required">
							<Checkbox
								id="_kudos_address_required"
								label="Address required"
								value={ props.settings._kudos_address_required }
								onChange={ props.handleInputChange }
							/>
						</Fragment>,
				  ]
				: '' }
		</PanelBody>
	);
};

export { DonationModalPanel };
