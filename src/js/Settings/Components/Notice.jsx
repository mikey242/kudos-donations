const { Snackbar } = wp.components;

const Notice = ( props ) => {
	if ( props.showNotice ) {
		console.log(props.message);
		return (
			<div className="components-snackbar-list components-editor-notices__snackbar">
				<Snackbar
					className={
						props.showNotice
							? 'notification-shown'
							: 'notification-hidden'
					}
					onRemove={ () => props.hideNotice() }
				>
					{ props.message }
				</Snackbar>
			</div>
		);
	}
	return null;
};

export { Notice };
