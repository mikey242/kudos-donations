import axios from 'axios';

const { __ } = wp.i18n;
const {
	PanelRow,
	PanelBody,
	BaseControl,
	TextControl,
	Button
} = wp.components;
const { useState } = wp.element;

const TestEmailPanel = ( props ) => {

	const [ isEdited, setIsEdited ] = useState( false );
	const [ email, setEmail ] = useState( '' );
	const [ isBusy, setIsBusy ] = useState( false );

	const validateEmail = () => {
		const emailReg = /^([\w-.]+@([\w-]+\.)+[\w-]{2,6})?$/;
		return emailReg.test( email );
	};

	const handleChange = ( value ) => {
		setIsEdited( true );
		setEmail( value );
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

		// Perform Post request
		axios
			.post(
				window.kudos.sendTestUrl,
				{
					email
				},
				{
					headers: {
						// eslint-disable-next-line no-undef
						'X-WP-Nonce': wpApiSettings.nonce,
					},
				}
			)
			.then( ( response ) => {
				console.log(response);
				props.showNotice( response.data.data );
				setIsBusy( false );
			} );
	};

	return (
		<PanelBody
			title={ __( 'Send Test Email', 'kudos-donations' ) }
			initialOpen={ false }
		>

			<PanelRow>
				<BaseControl
					label={ __( 'Email address', 'kudos-donations' ) }
					id={ 'test_email_address' }
					help={ __(
						'Make sure you save any changes beforehand.',
						'kudos-donations'
					) }
				>
					<TextControl
						id={ 'test_email_address' }
						type={ 'text' }
						value={ email }
						placeholder={ __(
							'user@domain.com',
							'kudos-donations'
						) }
						disabled={ props.disabled }
						onChange={ ( email ) => handleChange(email) }
					/>
				</BaseControl>
			</PanelRow>

			<PanelRow>
				<Button
					isPrimary
					disabled={ ! isEdited || isBusy }
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
