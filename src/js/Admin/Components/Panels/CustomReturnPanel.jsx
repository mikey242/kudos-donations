import { Toggle } from '../FormElements/Toggle';
import { TextInput } from '../FormElements/TextInput';

const { __ } = wp.i18n;

const { PanelBody } = wp.components;

const { Fragment } = wp.element;

const CustomReturnPanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'Custom Return URL', 'kudos-donations' ) }
			initialOpen={ false }
		>
			<Toggle
				id="_kudos_custom_return_enable"
				label={ __( 'Use custom return URL', 'kudos-donations' ) }
				help={ __(
					'After payment the customer is returned to the page where they clicked on the donation button. To use a different return URL, enable this option.',
					'kudos-donations'
				) }
				value={ props.settings._kudos_custom_return_enable }
				onChange={ props.handleInputChange }
			/>

			{ props.settings._kudos_custom_return_enable
				? [
						<Fragment key="_kudos_custom_return_fields">
							<TextInput
								id="_kudos_custom_return_url"
								label={ __( 'URL', 'kudos-donations' ) }
								help={ __(
									'e.g https://mywebsite.com/thanks',
									'kudos-donations'
								) }
								value={
									props.settings._kudos_custom_return_url
								}
								disabled={ props.isSaving }
								onChange={ props.handleInputChange }
							/>
						</Fragment>,
				  ]
				: '' }
		</PanelBody>
	);
};

export { CustomReturnPanel };
