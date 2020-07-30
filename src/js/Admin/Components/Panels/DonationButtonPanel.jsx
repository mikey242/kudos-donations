const { __ } = wp.i18n;

const { PanelBody } = wp.components;

import { TextInput } from '../FormElements/TextInput';
import { ColorPicker } from '../FormElements/ColorPicker';

const DonationButtonPanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'Donation Button', 'kudos-donations' ) }
			initialOpen={ false }
		>
			<ColorPicker
				id="_kudos_button_color"
				value={ props.settings._kudos_button_color }
				onChange={ props.handleInputChange }
			/>

			<TextInput
				id="_kudos_button_label"
				label={ __( 'Button text', 'kudos-donations' ) }
				value={ props.settings._kudos_button_label }
				placeHolder={ __( 'Button label', 'kudos-donations' ) }
				disabled={ props.isSaving }
				onChange={ props.handleInputChange }
			/>
		</PanelBody>
	);
};

export { DonationButtonPanel };
