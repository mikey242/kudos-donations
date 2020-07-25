import { TextInput } from '../FormElements/TextInput';
import { Checkbox } from '../FormElements/Checkbox';

const { __ } = wp.i18n;
const { PanelBody } = wp.components;
const { Fragment } = wp.element;

const DonationFormPanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'Donation Form', 'kudos-donations' ) }
			initialOpen={ false }
		>
			<Checkbox
				id="_kudos_address_enabled"
				heading={ __( 'Address', 'kudos-donations' ) }
				label="Enabled"
				value={ props.settings._kudos_address_enabled }
				onChange={ props.handleInputChange }
			/>

			{ props.settings._kudos_address_enabled
				? [
						<Fragment key="_kudos_address_required">
							<Checkbox
								id="_kudos_address_required"
								label="Required"
								value={ props.settings._kudos_address_required }
								onChange={ props.handleInputChange }
							/>
						</Fragment>,
				  ]
				: '' }

			<TextInput
				id="_kudos_form_header"
				label={ __( 'Payment form header', 'kudos-donations' ) }
				value={ props.settings._kudos_form_header }
				disabled={ props.isSaving }
				onChange={ props.handleInputChange }
			/>

			<TextInput
				id="_kudos_form_text"
				label={ __( 'Payment form text', 'kudos-donations' ) }
				value={ props.settings._kudos_form_text }
				disabled={ props.isSaving }
				onChange={ props.handleInputChange }
			/>
		</PanelBody>
	);
};

export { DonationFormPanel };
