const { __ } = wp.i18n;

const {
	PanelBody,
	PanelRow,
	BaseControl,
	TextControl,
	RadioControl,
	CheckboxControl,
	ToggleControl
} = wp.components;

const { Fragment } = wp.element;

const EmailSettingsPanel = ( props ) => {

	return (
		<PanelBody title={ __( 'Email Settings' ) } initialOpen={ false }>

			<PanelRow>
				<ToggleControl
					label={ 'Use custom email settings' }
					help={ 'Enable this to use your own SMTP server settings.' }
					checked={ props.settings._kudos_smtp_enable || '' }
					onChange={ () => props.handleInputChange( "_kudos_smtp_enable", ! props.settings._kudos_smtp_enable ) }
				/>
			</PanelRow>

			{ props.settings._kudos_smtp_enable
				? [

			<Fragment>

					<PanelRow>
						<BaseControl
							id={ "_kudos_smtp_host" }
							label={ __( 'Host', 'kudos-donations' ) }
							help={ __( "Your email server's hostname", 'kudos-donations' ) }
						>
							<TextControl
								id={ "_kudos_smtp_host" }
								type={ 'text' }
								value={ props.settings._kudos_smtp_host || '' }
								placeholder="mail.host.com"
								disabled={ props.isSaving }
								onChange={ ( value ) => props.handleInputChange( "_kudos_smtp_host", value ) }
							/>
						</BaseControl>
					</PanelRow>

					<PanelRow>
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
					</PanelRow>

					<PanelRow>
						<CheckboxControl
							heading={ __( 'Auto TLS', 'kudos-donations' ) }
							label={ __( 'Enable', 'kudos-donations' ) }
							help={ __(
								'In most cases you will want this enabled. Disable for troubleshooting.',
								'kudos-donations'
							) }
							checked={ props.settings._kudos_smtp_autotls || '' }
							onChange={ ( value ) => props.handleInputChange( '_kudos_smtp_autotls', value ) }
						/>
					</PanelRow>

					<PanelRow>
						<BaseControl
							id={ "_kudos_smtp_port" }
							label={ __( 'Port', 'kudos-donations' ) }
							help={ __(
								'587 (TLS), 465 (SSL), 25 (Unencrypted)',
								'kudos-donations'
							) }
						>
							<TextControl
								id={ "_kudos_smtp_port" }
								type={ 'text' }
								value={ props.settings._kudos_smtp_port || '' }
								placeholder="587"
								disabled={ props.isSaving }
								onChange={ ( value ) => props.handleInputChange( "_kudos_smtp_port", value ) }
							/>
						</BaseControl>
					</PanelRow>

					<PanelRow>
						<BaseControl
							id={ "_kudos_smtp_username" }
							label={ __( 'Username', 'kudos-donations' ) }
							help={ __(
								'This is usually an email address',
								'kudos-donations'
							) }
						>
							<TextControl
								id={ "_kudos_smtp_username" }
								type={ 'text' }
								value={ props.settings._kudos_smtp_username || '' }
								placeholder="user@domain.com"
								disabled={ props.isSaving }
								onChange={ ( value ) => props.handleInputChange( "_kudos_smtp_username", value ) }
							/>
						</BaseControl>
					</PanelRow>

					<PanelRow>
						<BaseControl
							id={ "_kudos_smtp_password" }
							label={ __( 'Password', 'kudos-donations' ) }
							type="password"
						>
							<TextControl
								id={ "_kudos_smtp_password" }
								type={ 'password' }
								value={ props.settings._kudos_smtp_password || '' }
								placeholder="*****"
								disabled={ props.isSaving }
								onChange={ ( value ) => props.handleInputChange( "_kudos_smtp_password", value ) }
							/>
						</BaseControl>
					</PanelRow>

					<PanelRow>
						<BaseControl
							id={ "_kudos_smtp_from" }
							label={ __( 'From address', 'kudos-donations' ) }
							help={ __(
								'The email address emails will appear to be sent from. Leave empty to use same as username.',
								'kudos-donations'
							) }
						>
							<TextControl
								id={ "_kudos_smtp_from" }
								type={ 'text' }
								value={ props.settings._kudos_smtp_from || '' }
								placeholder="user@domain.com"
								disabled={ props.isSaving }
								onChange={ ( value ) => props.handleInputChange( "_kudos_smtp_from", value ) }
							/>
						</BaseControl>
					</PanelRow>

				</Fragment>

			]
			: '' }

		</PanelBody>
	);
};

export { EmailSettingsPanel };
