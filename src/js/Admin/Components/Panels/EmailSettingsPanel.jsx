const { __ } = wp.i18n;

const { PanelBody } = wp.components;

const { Fragment } = wp.element;

import { Toggle } from '../FormElements/Toggle';
import { TextInput } from '../FormElements/TextInput';
import { RadioButtons } from '../FormElements/RadioButtons';
import { Checkbox } from '../FormElements/Checkbox';

const EmailSettingsPanel = ( props ) => {
	let optionalForm = (
		<Fragment>
			<TextInput
				id="_kudos_smtp_host"
				label={ __( 'Host', 'kudos-donations' ) }
				help={ __( "Your email server's hostname", 'kudos-donations' ) }
				value={ props.settings._kudos_smtp_host }
				placeholder="mail.host.com"
				onChange={ props.handleInputChange }
			/>
			<RadioButtons
				id="_kudos_smtp_encryption"
				label={ __( 'Encryption', 'kudos-donations' ) }
				help={ __(
					'Choose your encryption type. Always use TLS or SSL if available.',
					'kudos-donations'
				) }
				selected={ props.settings._kudos_smtp_encryption }
				onChange={ props.handleInputChange }
			>
				{ [
					{ label: __( 'None', 'kudos-donations' ), value: 'none' },
					{ label: __( 'SSL', 'kudos-donations' ), value: 'ssl' },
					{ label: __( 'TLS', 'kudos-donations' ), value: 'tls' },
				] }
			</RadioButtons>
			<Checkbox
				id="_kudos_smtp_autotls"
				heading={ __( 'Auto TLS', 'kudos-donations' ) }
				label={ __( 'Enable', 'kudos-donations' ) }
				help={ __(
					'In most cases you will want this enabled. Disable for troubleshooting.',
					'kudos-donations'
				) }
				value={ props.settings._kudos_smtp_autotls }
				onChange={ props.handleInputChange }
			/>
			<TextInput
				id="_kudos_smtp_port"
				label={ __( 'Port', 'kudos-donations' ) }
				help={ __(
					'587 (TLS), 465 (SSL), 25 (Unencrypted)',
					'kudos-donations'
				) }
				value={ props.settings._kudos_smtp_port }
				placeholder="587"
				onChange={ props.handleInputChange }
			/>
			<TextInput
				id="_kudos_smtp_username"
				label={ __( 'Username', 'kudos-donations' ) }
				help={ __(
					'This is usually an email address',
					'kudos-donations'
				) }
				value={ props.settings._kudos_smtp_username }
				placeholder="user@domain.com"
				onChange={ props.handleInputChange }
			/>
			<TextInput
				id="_kudos_smtp_password"
				label={ __( 'Password', 'kudos-donations' ) }
				type="password"
				help=""
				value={ props.settings._kudos_smtp_password }
				placeholder="*****"
				onChange={ props.handleInputChange }
			/>
			<TextInput
				id="_kudos_smtp_from"
				label={ __( 'From address', 'kudos-donations' ) }
				help={ __(
					'The email address emails will appear to be sent from. Leave empty to use same as username.',
					'kudos-donations'
				) }
				value={ props.settings._kudos_smtp_from }
				placeholder="user@domain.com"
				onChange={ props.handleInputChange }
			/>
		</Fragment>
	);

	if ( ! props.settings._kudos_smtp_enable ) {
		optionalForm = '';
	}

	return (
		<PanelBody title={ __( 'Email Settings' ) } initialOpen={ false }>
			<Toggle
				id="_kudos_smtp_enable"
				label={ 'Use custom email settings' }
				help={ 'Enable this to use your own SMTP server settings.' }
				value={ props.settings._kudos_smtp_enable }
				onChange={ props.handleInputChange }
			/>

			{ optionalForm }
		</PanelBody>
	);
};

export { EmailSettingsPanel };
