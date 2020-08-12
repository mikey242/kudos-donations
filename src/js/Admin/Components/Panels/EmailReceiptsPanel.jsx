const { __ } = wp.i18n;

const {
	PanelBody,
	PanelRow,
	BaseControl,
	TextControl,
	ToggleControl
} = wp.components;

const { Fragment } = wp.element;

const EmailReceiptsPanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'Email Receipts', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<PanelRow>
				<ToggleControl
					label={ __( 'Send email receipts', 'kudos-donations' ) }
					help={ __(
						'Once a payment has been completed, you can automatically send an email receipt to the donor.',
						'kudos-donations'
					) }
					checked={ props.settings._kudos_email_receipt_enable || '' }
					onChange={ () => props.handleInputChange( "_kudos_email_receipt_enable", ! props.settings._kudos_email_receipt_enable ) }
				/>
			</PanelRow>

			{ props.settings._kudos_email_receipt_enable
				? [

					<PanelRow>
						<BaseControl
							id={ "_kudos_email_bcc" }
							label={ __(
								'Send receipt copy to:',
								'kudos-donations'
							) }
						>
							<TextControl
								id={ "_kudos_email_bcc" }
								type={ 'text' }
								value={ props.settings._kudos_email_bcc || '' }
								placeholder={ props.placeholder }
								disabled={ props.isSaving }
								onChange={ ( value ) => props.handleInputChange( "_kudos_email_bcc", value ) }
							/>
						</BaseControl>
					</PanelRow>

				  ]
				: '' }
		</PanelBody>
	);
};

export { EmailReceiptsPanel };
