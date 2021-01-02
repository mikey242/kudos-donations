const { __ } = wp.i18n;
const {
	PanelBody,
	CheckboxControl,
	RadioControl,
	TextControl,
	ToggleControl
} = wp.components;

const {Fragment} = wp.element;

const EmailSettingsPanel = ( props ) => {

	return (
		<PanelBody title={ __( 'Email settings', 'kudos-donations' ) } initialOpen={ false }>

			<ToggleControl
				label={ __('Use custom email settings', 'kudos-donations') }
				help={ __('Enable this to use your own SMTP server settings.', 'kudos-donations') }
				checked={ props.settings._kudos_smtp_enable || '' }
				onChange={ () => props.handleInputChange( "_kudos_smtp_enable", ! props.settings._kudos_smtp_enable ) }
			/>

			{ props.settings._kudos_smtp_enable ?

				<Fragment>

					<TextControl
						label={ __( 'Host', 'kudos-donations' ) }
						help={ __( "Your email server's hostname", 'kudos-donations' ) }
						type={ 'text' }
						value={ props.settings._kudos_smtp_host || '' }
						placeholder="mail.host.com"
						disabled={ props.isSaving }
						onChange={ ( value ) => props.handleInputChange( "_kudos_smtp_host", value ) }
					/>


					<RadioControl
						id="_kudos_smtp_encryption"
						label={ __( 'Encryption', 'kudos-donations' ) }
						help={ __(
							'Choose your encryption type. Always use TLS or SSL if available.',
							'kudos-donations'
						) }
						selected={ props.settings._kudos_smtp_encryption }
						options={
							[
								{ label: __( 'None', 'kudos-donations' ), value: 'none' },
								{ label: __( 'SSL', 'kudos-donations' ), value: 'ssl' },
								{ label: __( 'TLS', 'kudos-donations' ), value: 'tls' },
							]
						}
						onChange={ ( value ) => props.handleInputChange( '_kudos_smtp_encryption', value ) }
					/>

					<CheckboxControl

						label={ __( 'Enable Auto TLS', 'kudos-donations' ) }
						help={ __(
							'In most cases you will want this enabled. Disable for troubleshooting.',
							'kudos-donations'
						) }
						checked={ props.settings._kudos_smtp_autotls || '' }
						onChange={ ( value ) => props.handleInputChange( '_kudos_smtp_autotls', value ) }
					/>

					<TextControl
						label={ __( 'Port', 'kudos-donations' ) }
						help={ __(
							'587 (TLS), 465 (SSL), 25 (Unencrypted)',
							'kudos-donations'
						) }
						type={ 'text' }
						value={ props.settings._kudos_smtp_port || '' }
						placeholder="587"
						disabled={ props.isSaving }
						onChange={ ( value ) => props.handleInputChange( "_kudos_smtp_port", value ) }
					/>

					<TextControl
						label={ __( 'Username', 'kudos-donations' ) }
						help={ __(
							'This is usually an email address',
							'kudos-donations'
						) }
						type={ 'text' }
						value={ props.settings._kudos_smtp_username || '' }
						placeholder="user@domain.com"
						disabled={ props.isSaving }
						onChange={ ( value ) => props.handleInputChange( "_kudos_smtp_username", value ) }
					/>

					<TextControl
						label={ __( 'Password', 'kudos-donations' ) }
						type="password"
						type={ 'password' }
						value={ props.settings._kudos_smtp_password || '' }
						placeholder="*****"
						disabled={ props.isSaving }
						onChange={ ( value ) => props.handleInputChange( "_kudos_smtp_password", value ) }
					/>

					<TextControl
						label={ __( 'From address', 'kudos-donations' ) }
						help={ __(
							'The email address emails will appear to be sent from. Leave empty to use same as username.',
							'kudos-donations'
						) }
						type={ 'text' }
						value={ props.settings._kudos_smtp_from || '' }
						placeholder="user@domain.com"
						disabled={ props.isSaving }
						onChange={ ( value ) => props.handleInputChange( "_kudos_smtp_from", value ) }
					/>

				</Fragment>

			: '' }

		</PanelBody>
	);
};

export { EmailSettingsPanel };
