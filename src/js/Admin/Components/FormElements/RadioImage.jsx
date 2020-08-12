import _uniqueId from 'lodash/uniqueId';

const { ButtonGroup, Button } = wp.components;

const RadioImage = ( props ) => {
	return (

			<ButtonGroup>
				{ props.children.map( ( child, index ) => {
					return (
						<Button
							isPrimary={ props.isPrimary }
							key={ child.value + '-' + index }
							disabled={ child.value === props.value }
							isPressed={ child.value === props.value }
							onClick={ () =>
								props.onClick( props.id, child.value )
							}
						>
							{ child.content }
						</Button>
					);
				} ) }
			</ButtonGroup>
	);
};

export { RadioImage };
