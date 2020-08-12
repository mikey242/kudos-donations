const { __ } = wp.i18n;

const {
	PanelBody,
	PanelRow,
	BaseControl,
	ToggleControl,
	TextControl,
	Dashicon
} = wp.components;

const { Fragment } = wp.element;

const CompletedPaymentModal = ( props ) => {
	return (
		<PanelBody
			title={ __( 'Completed Payment', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<PanelRow>
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
			</PanelRow>

			{ props.settings._kudos_return_message_enable
				? [
						<Fragment>

							<PanelRow>
								<BaseControl
									id={ "_kudos_return_message_header" }
									label={ __(
										'Header',
										'kudos-donations'
									) }
								>
									<TextControl
										id={ "_kudos_return_message_header" }
										type={ 'text' }
										value={ props.settings._kudos_return_message_header || '' }
										disabled={ props.isSaving }
										onChange={ ( value ) => props.handleInputChange( "_kudos_return_message_header", value ) }
									/>
								</BaseControl>
							</PanelRow>

							<PanelRow>
								<BaseControl
									id={ "_kudos_return_message_text" }
									label={ __(
										'Text',
										'kudos-donations'
									) }
								>
									<TextControl
										id={ "_kudos_return_message_text" }
										type={ 'text' }
										value={ props.settings._kudos_return_message_text || '' }
										placeHolder={ __(
											'Button label',
											'kudos-donations'
										) }
										disabled={ props.isSaving }
										onChange={ ( value ) => props.handleInputChange( "_kudos_return_message_text", value ) }
									/>
								</BaseControl>
							</PanelRow>

							<p className="components-kudos-info">
								<Dashicon icon="info" />
								<i>{ __(
									'You can use the following variables in the above fields: {{name}}, {{email}}, {{value}}'
								) }</i>
							</p>

						</Fragment>,
				  ]
				: '' }
		</PanelBody>
	);
};

export { CompletedPaymentModal };
