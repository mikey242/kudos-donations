const { __ } = wp.i18n;

const { PanelBody } = wp.components;

const DiagnosticsPanel = ( props ) => {
	return (
		<PanelBody
			title={ __( 'Diagnostics', 'kudos-donations' ) }
			initialOpen={ true }
		>
			<p>
				PHP Version: <span>{ props.phpVersion }</span>
			</p>
			<p>mbString: { props.mbstring ? 'True' : 'False' }</p>
			<p>
				Invoice Directory Writeable:{ ' ' }
				{ props.invoiceWriteable ? 'True' : 'False' }
			</p>
			<p>
				Log Directory Writeable:{ ' ' }
				{ props.logWriteable ? 'True' : 'False' }
			</p>
			<p>
				Permalink structure:{ ' ' }
				{ props.permalinkStructure
					? props.permalinkStructure
					: 'We recommend setting a non-plain permalink structure' }
			</p>
		</PanelBody>
	);
};

export { DiagnosticsPanel };
