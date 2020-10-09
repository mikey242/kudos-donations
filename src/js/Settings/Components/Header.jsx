import logo from '../../../img/logo-colour.svg'

const { __ } = wp.i18n;

const Header = ( props ) => {

	let status;
	let statusClass;

	if ( props.checkingApi ) {
		statusClass = 'checking';
		status = __('checking', 'kudos-donations');
	} else if ( props.apiConnected ) {
		statusClass = 'connected';
		status = __('connected', 'kudos-donations');
	} else if ( ! props.apiConnected ) {
		statusClass = 'not-connected';
		status = __('not-connected', 'kudos-donations');
	}

	return (
		<div className="kudos-dashboard-header kd-relative kd-bg-white kd-py-5">
			<div className="kd-container kd-flex kd-items-center kd-justify-between">
				<div className="kd-flex">
					<img width="30" src={ logo } className="kd-mr-4" alt="Kudos Logo" />
					<h1>{ __( 'Kudos Settings', 'kudos-donations' ) }</h1>
				</div>
				<div className="kudos-dashboard-header__right">
					<span
						style={ { textTransform: 'capitalize' } }
						className={ 'kudos-api-status ' + statusClass + ' kd-text-gray-600 kd-ml-4' }
					>
						{ status }
					</span>
					<span className="kudos-version kd-font-bold kd-rounded-lg kd-p-3 kd-ml-4 kd-border kd-border-solid kd-border-gray-400">
						{ window.kudos.version }
					</span>
				</div>
			</div>
		</div>
	);
};

export { Header };
