const { Snackbar } = wp.components;

const KudosNotice = ( props ) => {
	if ( props.showNotice ) {
		return (
			<div className="components-snackbar-list components-editor-notices__snackbar">
				<Snackbar
					id={ props.id }
					className={
						props.showNotice
							? 'notification-shown'
							: 'notification-hidden'
					}
					onRemove={ () => props.hideNotice( props.id ) }
				>
					{ props.children }
				</Snackbar>
			</div>
		);
	}
	return null;
};

export { KudosNotice };
