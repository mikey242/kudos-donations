import logo from '../../../img/logo-colour.svg'

const { __ } = wp.i18n;

const Header = ( props ) => {

	let status;

	if ( props.checkingApi ) {
		status = 'checking';
	} else if ( props.apiConnected ) {
		status = 'connected';
	} else if ( ! props.apiConnected ) {
		status = 'not-connected';
	}

	return (
		<div className="kudos-dashboard-header">
			<div className="dashboard-wrap flex items-center justify-between">
				<div className="flex">
					<img width="30" src={ logo } alt="Kudos Logo" />
					<h1>{ __( 'Kudos Settings', 'kudos-donations' ) }</h1>
				</div>
				<div className="kudos-dashboard-header__right">
					<span
						style={ { textTransform: 'capitalize' } }
						className={ 'kudos-api-status ' + status }
					>
						{ status }
					</span>
					<span className="kudos-version">
						{ window.kudos.version }
					</span>
				</div>
			</div>
		</div>
	);
};

export { Header };
