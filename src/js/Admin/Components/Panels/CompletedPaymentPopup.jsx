import { Toggle } from '../FormElements/Toggle';
import { TextInput } from '../FormElements/TextInput';

const { __ } = wp.i18n;

const { PanelBody } = wp.components;

const { Fragment } = wp.element;

const CompletedPaymentPopup = ( props ) => {
	return (
		<PanelBody
			title={ __( 'Completed Payment Pop-up', 'kudos-donations' ) }
			initialOpen={ false }
		>
			<Toggle
				id="_kudos_return_message_enable"
				label={ __(
					'Show pop-up message when payment complete',
					'kudos-donations'
				) }
				help={ __(
					'Enable this to show a pop-up thanking the customer for their donation.',
					'kudos-donations'
				) }
				value={ props.settings._kudos_return_message_enable }
				onChange={ props.handleInputChange }
			/>

			{ props.settings._kudos_return_message_enable
				? [
						<Fragment key="_kudos_return_message_fields">
							<TextInput
								id="_kudos_return_message_header"
								label={ __(
									'Message header',
									'kudos-donations'
								) }
								value={
									props.settings._kudos_return_message_header
								}
								disabled={ props.isSaving }
								onChange={ props.handleInputChange }
							/>
							<TextInput
								id="_kudos_return_message_text"
								label={ __(
									'Message text',
									'kudos-donations'
								) }
								value={
									props.settings._kudos_return_message_text
								}
								placeHolder={ __(
									'Button label',
									'kudos-donations'
								) }
								disabled={ props.isSaving }
								onChange={ props.handleInputChange }
							/>
						</Fragment>,
				  ]
				: '' }
		</PanelBody>
	);
};

export { CompletedPaymentPopup };
