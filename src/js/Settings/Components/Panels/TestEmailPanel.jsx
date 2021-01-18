const  { __ } = wp.i18n;
const { PanelBody, Button, PanelRow, TextControl } = wp.components;
const { useState } = wp.element;

const TestEmailPanel = ( props ) => {

	const [ buttonDisabled, setButtonDisabled ] = useState(true);
	const [ email, setEmail ] = useState( '' );
	const [ isBusy, setIsBusy ] = useState( false );

	const handleChange = ( value ) => {
		setEmail( value );
		setButtonDisabled(!validateEmail(value));
	}

	const validateEmail = (email) => {
		const emailReg = /^[\w-.]+@([\w-]+\.)+[\w-]{2,6}$/;
		return emailReg.test( email );
	};

	const sendTest = () => {

		setIsBusy( true );

		if ( ! validateEmail( email ) ) {
			setIsBusy( false );
			props.showNotice(
				__( 'Invalid email address', 'kudos-donations' )
			);
			return;
		}

		const requestOptions = {
			method: 'POST',
			// eslint-disable-next-line no-undef
			headers: {
				'X-WP-Nonce': wpApiSettings.nonce,
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({ email: email })
		};

		fetch(window.kudos.sendTestUrl, requestOptions)
			.then(response => response.json())
			.then((response) => {
				props.showNotice( response.data );
				setIsBusy( false );
			})
			.catch((error) => {
				props.showNotice( error.response.statusText );
				setIsBusy( false );
			})
	};

	return (
		<PanelBody
			title={ __( 'Send test email', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<TextControl
				label={ __( 'Email address', 'kudos-donations' ) }
				help={ __(
					'Make sure you save any changes beforehand.',
					'kudos-donations'
				) }
				type={ 'text' }
				value={ email }
				placeholder={ __(
					'user@domain.com',
					'kudos-donations'
				) }
				disabled={ props.disabled }
				onChange={ ( email ) => handleChange(email) }
			/>

			<PanelRow>
				<Button
					isPrimary
					isSmall
					disabled={ buttonDisabled || isBusy }
					isBusy={ isBusy }
					onClick={ () => {
						sendTest( email );
					} }
				>
					{isBusy ? __('Sending', 'kudos-donations') : __('Send', 'kudos-donations')}
				</Button>
			</PanelRow>

		</PanelBody>
	);
};

export { TestEmailPanel };
