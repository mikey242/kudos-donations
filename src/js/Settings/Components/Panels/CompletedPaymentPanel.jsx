import {Info} from "../Info"

const {__} = wp.i18n;
const { PanelBody, TextControl, ToggleControl } = wp.components;
const { Fragment } = wp.element;

const CompletedPaymentPanel = (props ) => {
	return (
		<PanelBody
			title={ __( 'Completed Payment', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<ToggleControl
				label={ __(
					'Show pop-up message when payment complete',
					'kudos-donations'
				) }
				help={ __(
					'Enable this to show a pop-up thanking the customer for their donation.',
					'kudos-donations'
				) }
				checked={ props.settings._kudos_return_message_enable || '' }
				onChange={ () => props.handleInputChange( "_kudos_return_message_enable", ! props.settings._kudos_return_message_enable ) }
			/>

			{ props.settings._kudos_return_message_enable ?

				<Fragment>

					<TextControl
						label={ __( 'Title', 'kudos-donations'	) }
						type={ 'text' }
						value={ props.settings._kudos_return_message_title || '' }
						disabled={ props.isSaving }
						onChange={ ( value ) => props.handleInputChange( "_kudos_return_message_title", value ) }
					/>

					<TextControl
						label={ __( 'Text', 'kudos-donations' ) }
						type={ 'text' }
						value={ props.settings._kudos_return_message_text || '' }
						placeholder={ __(
							'Button label',
							'kudos-donations'
						) }
						disabled={ props.isSaving }
						onChange={ ( value ) => props.handleInputChange( "_kudos_return_message_text", value ) }
					/>

					<Info>
						{ __( 'You can use the following variables in the above fields: {{name}}, {{email}}, {{value}}', 'kudos-donations') }
					</Info>

				</Fragment>

			: '' }

		</PanelBody>
	);
};

export { CompletedPaymentPanel };
