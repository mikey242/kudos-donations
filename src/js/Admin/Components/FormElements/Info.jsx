const { Dashicon } = wp.components;

const Info = ( props ) => {
	return (
		<p className="components-kudos-info">
			<Dashicon icon="info" />
			<i>{ props.text }</i>
		</p>
	);
};

export { Info };
