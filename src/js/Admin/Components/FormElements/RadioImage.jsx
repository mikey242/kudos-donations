import _uniqueId from 'lodash/uniqueId';

const { PanelRow, BaseControl, ButtonGroup, Button } = wp.components;

const RadioImage = ( props ) => {
	return (
		<PanelRow>
			<BaseControl
				label={ props.label }
				id={ _uniqueId( props.label ) }
				help={ props.help }
				className={
					'components-kudos-radio-buttons' +
					( props.className ? ' ' + props.className : '' )
				}
			>
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
			</BaseControl>
		</PanelRow>
	);
};

export { RadioImage };
